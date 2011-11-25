#!/usr/bin/env python
#-*- coding: utf-8 -*-
#Copyright (C) 2011 ISVTEC SARL
#$Id$
__author__ = "Ousmane Wilane ♟ <ousmane@wilane.org>"
__date__   = "Fri Nov 11 09:43:42 2011"

from django.contrib.auth.decorators import login_required
from django.shortcuts import render, redirect, get_object_or_404, Http404
from django.conf import settings
from django.http import HttpResponse
from django.views.decorators.http import require_http_methods
from django.core.urlresolvers import reverse
from django.utils.translation import ugettext_lazy as _
from os.path import join
from subprocess import call
import operator
from fo.hipay import hipay as HP
from fo.enterprise.models import Users
from fo.invoice.models import Invoices, Transaction

@login_required
def list_companies(request):
    try:
        current_user = Users.objects.get(email=request.user.email)
        # We keep a loose coupling with native Users database from the backend
        customer_list = current_user.clients_set.all()
        
    except Users.DoesNotExist:
        customer_list = None

    return render(request,'invoice/list_companies.html',
                              {'customer_list': customer_list})
    
@login_required
def list_invoices(request, customer_id, message=None):
    current_user = Users.objects.get(email=request.user.email)
    customer = get_object_or_404(current_user.clients_set, id_client=customer_id)    
    return render(request, 'invoice/list_invoices.html',
                              {'invoice_list': customer.invoices_set.filter(type_doc='facture', is_paye=False),
                               'quote_list': customer.invoices_set.filter(type_doc='devis'),
                               'company': customer,
                               'message':message})

@login_required
def detail_invoice(request, invoice_id):
    current_user = Users.objects.get(email=request.user.email)
    invoices = [c.invoices_set.all() for c in current_user.clients_set.all()]
    if not invoices:
        raise Http404
    qs  = reduce(operator.or_, invoices)
    invoice = get_object_or_404(qs, id_facture=invoice_id)    
    
    return render(request, 'invoice/detail_invoices.html',
                              {'invoice':invoice,
                               'invoice_details': invoice.invoicerows_set.order_by('ordre')})

@login_required
def accept_quote(request, invoice_id):
    current_user = Users.objects.get(email=request.user.email)
    invoices = [c.invoices_set.all() for c in current_user.clients_set.all()]
    if not invoices:
        # It's likely that you've keyed in an invoice id
        raise Http404
    
    qs  = reduce(operator.or_,invoices)
    quote = get_object_or_404(qs, id_facture=invoice_id)    
    quote.type_doc = 'facture'
    quote.save()
    return redirect('list_invoices', customer_id=quote.client.id_client)

@login_required
def hipay_invoice(request, invoice_id):
    current_user = Users.objects.get(email=request.user.email)
    invoices = [c.invoices_set.all() for c in current_user.clients_set.all()]
    if not invoices:
        raise Http404


    qs  = reduce(operator.or_,invoices)
    invoice = get_object_or_404(qs, id_facture=invoice_id)

    base_host = "http%s://%s" %('s' if request.is_secure() else '',
                                request.get_host())
    # FIXME: All these params are shop parameters, the website might have more
    # than one configured shops
    s = HP.PaymentParams("84971", "84971", "84971", "84971", "84971")
    s.setBackgroundColor('#FFFFFF')
    s.setCaptureDay('6')
    s.setCurrency('EUR')
    s.setLocale('fr_FR')
    s.setEmailAck('test@example.org')
    s.setLogin('84971', '313666')
    s.setMedia('WEB')
    s.setRating('+18')
    s.setIdForMerchant('142545')
    s.setMerchantSiteId('3194')
    
    # Will get this back from the IPN ACK 
    s.setMerchantDatas({'invoice_id':invoice_id, 'customer':invoice.client.id_client})
    s.setURLOk("%s%s" % (base_host, reverse('hipay_payment_url', kwargs={'invoice_id':invoice_id, 'action':'ok'})))
    s.setURLNok("%s%s" % (base_host, reverse('hipay_payment_url', kwargs={'invoice_id':invoice_id,'action':'nook'})))
    s.setURLCancel("%s%s" % (base_host, reverse('hipay_payment_url', kwargs={'invoice_id':invoice_id,'action':'cancel'})))
    s.setURLAck("%s%s" % (base_host, reverse('hipay_ipn_ack', kwargs={'invoice_id':invoice_id})))
    s.setLogoURL("%s%s" % (base_host, reverse('hipay_shop_logo')))

    products = HP.Product()
    taxes = HP.Tax('tax')
    mestax=[dict(taxName='TVA', taxVal=invoice.tax, percentage='true')]
    taxes.setTaxes(mestax)
    list_products = []
    for ligne in invoice.invoicerows_set.all():
        list_products.append({'name':ligne.description,
                         'info':ligne.description,
                         'quantity':int(ligne.qtt),
                         'ref':invoice.num_facture,
                         'category':'91', # https://test-payment.hipay.com/order/list-categories/id/3194
                         'price':ligne.prix_ht,
                         'tax':taxes})
    products.setProducts(list_products)
        
    order = HP.Order()
        
    data = [{'shippingAmount':0,
             'insuranceAmount':0,
             'fixedCostAmount':0, #sum([l.qtt*l.prix_ht for l in invoice.invoicerows_set.all()]),
             'orderTitle':'Mon ordre',
             'orderInfo':'Box',
             'orderCategory':91}]
    order.setOrders(data)
    pay = HP.HiPay(s)
    
    pay.SimplePayment(order, products)

    response = pay.SendPayment(settings.HIPAY_GATEWAY)

    if response['status'] == 'Accepted':
        return redirect(response['message'])
    else:
        raise ValueError(response)

@login_required
def download_invoice(request, invoice_id):
    #FIXME: Make the the called script accept a configurable directory, Cyril
    #can you fix that please.
    current_user = Users.objects.get(email=request.user.email)
    invoices = [c.invoices_set.all() for c in current_user.clients_set.all()]
    if not invoices:
        raise Http404

    qs  = reduce(operator.or_,invoices)
    invoice = get_object_or_404(qs, id_facture=invoice_id)
    if not call([settings.INVOICE_PDF_GENERATOR, str(invoice.id_facture)]):
        filename = 'Facture_%s_%s.pdf' %(invoice.num_facture, invoice.client.nom)
        filename = filename.replace(' ', '_')
        filepath = join(settings.INVOICE_PDF_DIR, filename)        
        response = HttpResponse(mimetype='application/pdf')
        response['Content-Disposition'] = 'attachment; filename=%s' %(filename,)
        response.write(open(filepath).read())
        return response

    raise Http404


def hipay_payment_url(request, invoice_id, action):
    """URL to redirect the client on canceled payment by the customer"""
    invoice = get_object_or_404(Invoices, id_facture=invoice_id)
    return render(request, 'invoice/hipay/%s_payment.html'%(action,), {'invoice':invoice})
    

@require_http_methods(["POST"])
def hipay_ipn_ack(request, invoice_id):
    """URL that get the ack from HIPAY"""
    # FIXME: We should check where this is coming from, if not, anybody could
    # pretend notifying for a payment that actually never happened ? I can't
    # figure out how they do this with MAPI
 
    invoice = get_object_or_404(Invoices, id_facture=invoice_id)
    res = HP.ParseAck(request.POST.get('xml', None))
    if res and res.get('status', None) == 'ok':
        invoice.is_paye = True
        invoice.save()
        # Save the transaction for future reference
        Transaction.objects.create(**res)

    # This is a bot that doesn't care
    return HttpResponse("")
                         
    
def hipay_shop_logo(request):
    pass
