#!/usr/bin/env python
#-*- coding: utf-8 -*-
#Copyright (C) 2011 ISVTEC SARL
#$Id$
__author__ = "Ousmane Wilane ♟ <ousmane@wilane.org>"
__date__   = "Fri Nov 11 09:43:42 2011"

from django.contrib.auth.decorators import login_required
from fo.invoice.models import Invoices
from fo.enterprise.models import Clients, Users
from django.shortcuts import render, redirect, get_object_or_404, Http404
from subprocess import call
from django.conf import settings
from django.http import HttpResponse
from os.path import join
import operator
from fo.hipay import hipay as HP
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
def list_invoices(request, customer_id):
    current_user = Users.objects.get(email=request.user.email)
    customer = get_object_or_404(current_user.clients_set, id_client=customer_id)    
    return render(request, 'invoice/list_invoices.html',
                              {'invoice_list': customer.invoices_set.filter(type_doc='facture', is_paye=False),
                               'quote_list': customer.invoices_set.filter(type_doc='devis'),
                               'company': customer})

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
    # FIXME: All these params are shop parameters, the website might have more
    # than one configured shops
    s = HP.PaymentParams("84971", "84971", "84971", "84971", "84971")
    s.setBackgroundColor('#234567')
    s.setCaptureDay('6')
    s.setCurrency('EUR')
    s.setLocale('fr_FR')
    s.setEmailAck('test@example.org')
    s.setLogin('84971', '313666')
    s.setMedia('WEB')
    s.setRating('+18')
    s.setIdForMerchant('142545')
    s.setMerchantSiteId('3194')
    s.setMerchantDatas({'alpha':23, 'beta':34})
    s.setURLOk("http://example.org/hipay/result/ok")
    s.setURLNok("http://example.org/hipay/result/ko")
    s.setURLCancel("http://example.org/hipay/result/cancel")
    s.setURLAck("http://example.org/hipay/result/ack")
    s.setLogoURL("http://example.org/hipay/shop/logo")

    products = HP.Product()
    taxes = HP.Tax('tax')
    mestax=[dict(taxName='TVA', taxVal=invoice.tax, percentage='true')]
    taxes.setTaxes(mestax)
    list_products = list()
    for ligne in invoice.invoicerows_set.all():
        list_products.append({'name':ligne.description,
                         'info':ligne.description,
                         'quantity':int(ligne.qtt),
                         'ref':invoice.num_facture,
                         'category':'91',
                         'price':ligne.prix_ht,
                         'tax':taxes})
    products.setProducts(list_products)
        
    order = HP.Order()
        
    data = [{'shippingAmount':0,
             'insuranceAmount':0,
             'fixedCostAmount':sum([l.qtt*l.prix_ht for l in invoice.invoicerows_set.all()]),
             'orderTitle':'Mon ordre',
             'orderInfo':'Box',
             'orderCategory':91}]
    order.setOrders(data)
    pay = HP.HiPay(s)
    
    pay.SimplePayment(order, products)

    response = pay.SendPayment("https://test-payment.hipay.com/order/")

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
        filepath = join(settings.INVOICE_PDF_DIR, filename)        
        response = HttpResponse(mimetype='application/pdf')
        response['Content-Disposition'] = 'attachment; filename=%s' %(filename,)
        response.write(open(filepath).read())
        return response

    raise Http404
