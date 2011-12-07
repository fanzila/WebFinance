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
from os.path import join
from subprocess import call
import operator
from fo.enterprise.models import Users
from fo.invoice.models import Invoices, InvoiceTransaction, SubscriptionTransaction, Subscription
from fo.libs import hipay
from fo.hipay.hipay import ParseAck
from django.views.decorators.csrf import csrf_exempt

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
                               'subscription_list': customer.subscription_set.filter(type_doc='invoice'),
                               'subscrptionquote_list': customer.subscription_set.filter(type_doc='quote'),
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
def detail_subscription(request, subscription_id):
    current_user = Users.objects.get(email=request.user.email)
    subscriptions = [c.subscription_set.all() for c in current_user.clients_set.all()]
    if not subscriptions:
        raise Http404
    qs  = reduce(operator.or_, subscriptions)
    subscription = get_object_or_404(qs, pk=subscription_id)

    return render(request, 'invoice/detail_subscriptions.html',
                              {'subscription':subscription,
                               'subscription_details': subscription.subscriptionrow_set.order_by('id')})

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
def accept_subscriptionquote(request, subscription_id):
    current_user = Users.objects.get(email=request.user.email)
    subscriptions = [c.subscription_set.all() for c in current_user.clients_set.all()]
    if not subscriptions:
        # It's likely that you've keyed in an invoice id
        raise Http404

    qs  = reduce(operator.or_,subscriptions)
    quote = get_object_or_404(qs, pk=subscription_id)
    quote.type_doc = 'invoice'
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
    tr = InvoiceTransaction(invoice=invoice)
    tr.save()
    response = hipay.simplepayment(invoice, sender_host=request.get_host(), secure=request.is_secure(), internal_transid=tr.pk, payment_type="invoice")

    if response['status'] == 'Accepted':
        tr.redirect_url = response['message']
        tr.save()
        return redirect(response['message'])
    else:
        tr.delete()
        raise ValueError(response)

@login_required
def hipay_subscription(request, subscription_id):
    current_user = Users.objects.get(email=request.user.email)
    subscriptions = [c.subscription_set.all() for c in current_user.clients_set.all()]
    if not subscriptions:
        raise Http404

    qs  = reduce(operator.or_, subscriptions)
    subscription = get_object_or_404(qs, pk=subscription_id)
    tr = SubscriptionTransaction(subscription=subscription)
    tr.save()

    response = hipay.subscriptionpayment(subscription, sender_host=request.get_host(), secure=request.is_secure(), internal_transid=tr.pk, payment_type="subscription")

    if response['status'] == 'Accepted':
        tr.redirect_url = response['message']
        tr.save()

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


@login_required
def download_subscription(request, subscription_id):
    #FIXME: Make the the called script accept a configurable directory, Cyril
    #can you fix that please.
    current_user = Users.objects.get(email=request.user.email)
    subscriptions = [c.subscription_set.all() for c in current_user.clients_set.all()]
    if not subscriptions:
        raise Http404

    qs  = reduce(operator.or_,subscriptions)
    subscription = get_object_or_404(qs, pk=subscription_id)
    if not call([settings.INVOICE_PDF_GENERATOR, str(subscription.pk)]):
        filename = 'Facture_%s_%s.pdf' %(subscription.ref_contrat, subscription.client.nom)
        filename = filename.replace(' ', '_')
        filepath = join(settings.INVOICE_PDF_DIR, filename)
        response = HttpResponse(mimetype='application/pdf')
        response['Content-Disposition'] = 'attachment; filename=%s' %(filename,)
        response.write(open(filepath).read())
        return response

    raise Http404

def hipay_payment_url(request, invoice_id, internal_transid, action, payment_type):
    """URL to redirect the client on canceled payment by the customer"""
    if payment_type == 'invoice':
        c_object = get_object_or_404(Invoices, id_facture=invoice_id)
        tr = c_object.invoicetransaction_set.get(pk=internal_transid)
    else:
        c_object = get_object_or_404(Subscription, pk=invoice_id)
        tr = c_object.subscriptiontransaction_set.get(pk=internal_transid)
    tr.first_status = action
    tr.save()
    return render(request, 'invoice/hipay/%s_payment.html'%(action,), {'payment_type':payment_type,'invoice':c_object})
    

@require_http_methods(["POST"])
@csrf_exempt
def hipay_ipn_ack(request, internal_transid, invoice_id, payment_type):
    """URL that get the ack from HIPAY"""
    # FIXME: We should check where this is coming from, if not, anybody could
    # pretend notifying for a payment that actually never happened ? I can't
    # figure out how they do this with MAPI

    if payment_type == 'invoice':
        c_object = get_object_or_404(Invoices, pk=invoice_id)
        qs = c_object.invoicetransaction_set.filter(pk=internal_transid)
    else:
        c_object = get_object_or_404(Subscription, pk=invoice_id)
        qs = c_object.subscriptiontransaction_set.filter(pk=internal_transid)

    res = ParseAck(request.POST.get('xml', None))
    if res and res.get('status', None) == 'ok':
        if payment_type == 'invoice':
            c_object.is_paye = True
            c_object.save()
        # Save the transaction for future reference
        qs.update(**res)

    # This is a bot that doesn't care
    return HttpResponse("")
                         
    
def hipay_shop_logo(request):
    pass

@login_required
def home(request):
    try:
        current_user = Users.objects.get(email=request.user.email)
        # We keep a loose coupling with native Users database from the backend
        customer_list = current_user.clients_set.all()
    except Users.DoesNotExist:
        customer_list = None

    if  customer_list and customer_list.count() == 1:
        return redirect(reverse('list_invoices', kwargs={'customer_id':customer_list[0].pk}))
    return redirect(reverse('list_companies'))
