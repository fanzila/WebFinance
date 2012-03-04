#!/usr/bin/env python
#-*- coding: utf-8 -*-
#Copyright (C) 2011 ISVTEC SARL
#$Id$
from __future__ import with_statement
__author__ = "Ousmane Wilane ♟ <ousmane@wilane.org>"
__date__   = "Fri Nov 11 09:43:42 2011"

import logging
import operator
from os.path import join, isfile
from os import unlink
from datetime import datetime
from subprocess import Popen, PIPE
from django.contrib.auth.decorators import login_required
from django.shortcuts import render, redirect, get_object_or_404, Http404
from django.conf import settings
from django.http import HttpResponse
from django.views.decorators.http import require_http_methods
from django.core.urlresolvers import reverse
from django.views.decorators.csrf import csrf_exempt
from enterprise.models import Users
from invoice.models import Invoices, InvoiceTransaction, SubscriptionTransaction, Subscription
from invoice.tasks import ipn_ping
from libs import hipay
from libs.utils import fo_get_template
from hipay.hipay import ParseAck

try:
    import json
except ImportError:
    import simplejson as json

#from gevent import monkey
#monkey.patch_all()


logger = logging.getLogger('wf')


@login_required
def list_companies(request):
    try:
        current_user = Users.objects.get(email=request.user.email)
        # We keep a loose coupling with native Users database from the backend
        customer_list = current_user.clients_set.all()

    except Users.DoesNotExist:
        customer_list = None

    return render(request, fo_get_template(request.get_host(),'invoice/list_companies.html'),
                              {'customer_list': customer_list})

@login_required
def list_invoices(request, customer_id, message=None):
    current_user = Users.objects.get(email=request.user.email)
    customer = get_object_or_404(current_user.clients_set, pk=customer_id)
    return render(request, fo_get_template(request.get_host(),'invoice/list_invoices.html'),
                              {'invoice_list': customer.invoices_set.filter(type_doc='facture', paid=False),
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
    invoice = get_object_or_404(qs, pk=invoice_id)

    return render(request, fo_get_template(request.get_host(),'invoice/detail_invoices.html'),
                              {'invoice':invoice,
                               'invoice_details': invoice.invoicerows_set.order_by('order')})


@login_required
def detail_subscription(request, subscription_id):
    current_user = Users.objects.get(email=request.user.email)
    subscriptions = [c.subscription_set.all() for c in current_user.clients_set.all()]
    if not subscriptions:
        raise Http404
    qs  = reduce(operator.or_, subscriptions)
    subscription = get_object_or_404(qs, pk=subscription_id)

    return render(request, fo_get_template(request.get_host(),'invoice/detail_subscriptions.html'),
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
    quote = get_object_or_404(qs, pk=invoice_id)
    quote.type_doc = 'facture'
    quote.save()
    return redirect('list_invoices', customer_id=quote.client.pk)


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
    return redirect('list_invoices', customer_id=quote.client.pk)

@login_required
def hipay_invoice(request, invoice_id):
    current_user = Users.objects.get(email=request.user.email)
    invoices = [c.invoices_set.all() for c in current_user.clients_set.all()]
    if not invoices:
        raise Http404

    qs  = reduce(operator.or_,invoices)
    invoice = get_object_or_404(qs, pk=invoice_id)
    tr = InvoiceTransaction(invoice=invoice)
    tr.save()
    response = hipay.simplepayment(invoice, sender_host=request.get_host(), secure=request.is_secure(), internal_transid=tr.pk)

    if response['status'] == 'Accepted':
        tr.redirect_url = response['message']
        tr.save()
        return redirect(response['message'])
    else:
        tr.delete()
        raise ValueError(response)

@login_required
def hipay_subscription(request, subscription_id):
    host = "http%s://%s" %('s' if request.is_secure() else '', request.get_host())
    current_user = Users.objects.get(email=request.user.email)
    subscriptions = [c.subscription_set.all() for c in current_user.clients_set.all()]
    if not subscriptions:
        raise Http404

    qs  = reduce(operator.or_, subscriptions)
    subscription = get_object_or_404(qs, pk=subscription_id)

    # We create an invoice for the first payment and the subsequent payments are
    # created by a celery task using the first=False to get the payment details
    first_invoice = Invoices.objects.create(client=subscription.client,
                                            invoice_num=subscription.ref_contrat,
                                            period=subscription.period,
                                            subscription=subscription
                                            )
    tr = InvoiceTransaction.objects.create(invoice=first_invoice)

    try:
        sr = subscription.subscriptionrow_set.get(first=True)
    except Subscription.DoesNotExist:
        raise ValueError("Malformed subscription %s, if this is a legacy subscription it should have been fixed by now"%subscription.pk)
    first_invoice.invoicerows_set.create(description=sr.description,
                                         qty=sr.qty,
                                         df_price=sr.price_excl_vat)
    response = hipay.simplepayment(first_invoice, sender_host=request.get_host(), secure=request.is_secure(), internal_transid=tr.pk)

    # This is the way we daeal with HiPay subscriptions, for now we don't want
    # it but we can get back to using it when it gets 'better'
    #response = hipay.subscriptionpayment(subscription, sender_host=request.get_host(), secure=request.is_secure(), internal_transid=tr.pk)

    if response['status'] == 'Accepted':
        tr.redirect_url = response['message']
        tr.save()
        tr.send_invoice_notice(host)
        return redirect(response['message'])
    else:
        raise ValueError(response)

@login_required
def download_invoice(request, invoice_id):
    #FIXME: Make the called script accept a configurable directory, Cyril can
    #you fix that please ... that one is fixed, can you please allow the script
    #to deal with subscriptions too
    current_user = Users.objects.get(email=request.user.email)
    invoices = [c.invoices_set.all() for c in current_user.clients_set.all()]
    if not invoices:
        raise Http404

    qs  = reduce(operator.or_,invoices)
    invoice = get_object_or_404(qs, pk=invoice_id)
    output, error = Popen([settings.INVOICE_PDF_GENERATOR, 'stdout', str(invoice.pk)], stdout=PIPE, stderr=PIPE).communicate()
    if not error:
        filename = 'Facture_%s_%s.pdf' %(invoice.invoice_num, invoice.client.name)
        filename = filename.replace(' ', '_')
        response = HttpResponse(mimetype='application/pdf')
        response['Content-Disposition'] = 'attachment; filename=%s' %(filename,)
        response.write(output)
        return response
    else:
        logger.warn(u"The script %s have failed (%s); invoice id is %s" %(settings.INVOICE_PDF_GENERATOR,error,invoice.pk))

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
    output, error = Popen([settings.INVOICE_PDF_GENERATOR, 'stdout', str(subscription.pk)], stdout=PIPE, stderr=PIPE).communicate()
    if not error:
        filename = 'Facture_%s_%s.pdf' %(subscription.ref_contrat, subscription.client.name)
        filename = filename.replace(' ', '_')
        response = HttpResponse(mimetype='application/pdf')
        response['Content-Disposition'] = 'attachment; filename=%s' %(filename,)
        response.write(output)
        return response
    else:
        logger.warn(u"The script %s have failed (%s); subscription id is %s" %(settings.INVOICE_PDF_GENERATOR,error, subscription.pk))

    raise Http404

def hipay_payment_url(request, invoice_id, internal_transid, action, payment_type):
    """URL to redirect the client on canceled payment by the customer"""
    if payment_type == 'invoice':
        c_object = get_object_or_404(Invoices, pk=invoice_id)
        tr = c_object.invoicetransaction_set.get(pk=internal_transid)
    else:
        c_object = get_object_or_404(Subscription, pk=invoice_id)
        tr = c_object.subscriptiontransaction_set.get(pk=internal_transid)
    tr.first_status = action
    tr.save()
    return render(request, fo_get_template(request.get_host(),'invoice/hipay/%s_payment.html'%(action,)), {'payment_type':payment_type,'invoice':c_object})


@require_http_methods(["POST"])
@csrf_exempt
def ack_postback(request):
    logger.info("Checking if the data came from us")
    data = request.POST.get('payment', None)
    payment = json.loads(data)
    transaction = None
    if len(payment) == 1:
        payment = payment[0]
        if payment['model'] == "invoice.invoicetransaction":
            try:
                transaction = InvoiceTransaction.objects.get(**payment['fields'])
            except InvoiceTransaction.DoesNotExist:
                logger.exception("Attempting to verify a non existent payment supposedly to be sent by us: %s"%(data,))
        elif payment['model'] == "invoice.subscriptiontransaction":
            try:
                transaction = SubscriptionTransaction.objects.get(**payment['fields'])
            except SubscriptionTransaction.DoesNotExist:
                logger.exception("Attempting to verify a non existent payment supposedly to be sent by us: %s"%(data,))
        if transaction: #and transaction.pinged_back:
            return HttpResponse("VERIFIED")

    logger.info("Checked the data and failed to validate that we've sent it")
    return HttpResponse("FAILED")


#@require_http_methods(["POST"])
@csrf_exempt
def test_url_ack(request):
    """ This is just a test to show how an ACK would look like from your application"""
    logger.info("Checking the data against the documented postback url")

    url_postback = "http://127.0.0.1:8000%s"%reverse('ack_postback')

    try:
        response = ack_postback(request)
        logger.info(u"Posting back to make sure we get it from the right bot")
    except Exception, e:
        logger.exception(u"Unable to postback %s, we've got an ack message to check %s" %(url_postback, e))
    logger.info(response.read())
    if response == "VERIFIED":
        return HttpResponse("Thanks, getting back to you to check this is you")

    return HttpResponse("May the force be with you")

@require_http_methods(["POST"])
@csrf_exempt
def hipay_ipn_ack(request, internal_transid, invoice_id, payment_type):
    """URL that get the ack from HIPAY"""
    # FIXME: We should check where this is coming from, if not, anybody could
    # pretend notifying for a payment that actually never happened ? I can't
    # figure out how they do this with MAPI
    host = "http%s://%s" %('s' if request.is_secure() else '', request.get_host())
    if request.META.get('REMOTE_ADDR', None) not in settings.HIPAY_ACK_SOURCE_IPS:
        # We have to log this incident
        logger.critical(u"""Connexion from %s pretending to be ack server from HiPay,
                       the posted data is:%s;
                       TARGETED DATA:
                       internal_transid=%s;
                       invoice_id=%s;
                       payment_type=%s"""%(request.META.get('REMOTE_ADDR', None),
                                           request.POST.get('xml', None),
                                           internal_transid,
                                           invoice_id,
                                           payment_type))
        return HttpResponse(u"Thanks for your time")

    logger.debug(u"""Connexion from %s HiPay ACKing,
                    the posted data is:%s;
                    internal_transid=%s;
                    invoice_id=%s;
                    payment_type=%s"""%(request.META.get('REMOTE_ADDR', None),
                                        request.POST.get('xml', None),
                                        internal_transid,
                                        invoice_id,
                                        payment_type))

    if payment_type == 'invoice':
        c_object = get_object_or_404(Invoices, pk=invoice_id)
        # I use filter instead of get to have a qs returned so we can apply update on the dict
        first = c_object.invoicetransaction_set.get(pk=internal_transid)
        theset = c_object.invoicetransaction_set

    else:
        c_object = get_object_or_404(Subscription, pk=invoice_id)
        first = c_object.subscriptiontransaction_set.get(pk=internal_transid)
        theset = c_object.subscriptiontransaction_set

    result = ParseAck(request.POST.get('xml', None))
    if result:
        # FIXME: Make sure that this means PAID the $$$ are in my account and
        # nothing can change that ... money things are tricky

        # Save the transaction for future reference
        tr = theset.create(**result)
        tr.url_ack = first.url_ack
        tr.save()

        if result.get('status', None) == 'ok' and result.get('operation') == 'capture' and payment_type == 'invoice':
            c_object.paid = True
            c_object.payment_date = datetime.now()
            c_object.save()

            if first.url_ack:
                task_ipn_result = ipn_ping.delay(c_object.id) # Maybe save this for future ref ?!

            # Send the notice
            tr.payment_received_notice(host)

            # Test if this payment is linked to a subscription
            if c_object.subscription:
                c_object.subscription.paid = True
                c_object.payment_date = datetime.now()
                c_object.subscription.status = 'running' #Maybe check the previous status ?
                c_object.subscription.save()
                c_object.subscription.set_expiration_date()
                if c_object.subscription.status_url:
                    task_status_result = ipn_subscription.delay(c_object.subscription.id) # Maybe save this for future ref ?!



        if payment_type == 'invoice':
            if (result.get('status', None) == 'nok' and result.get('operation') == 'capture') or (result.get('status', None) == 'ok' and result.get('operation', None) in ('cancellation', 'refund', 'reject')):
                tr.payment_failure_notice(host)
                # FIXME: Ping Back the app for the subscription OK simple ping
    else:
        logger.debug("""Parsing data from IPN/%s failed %s""" %(request.META.get('REMOTE_ADDR', None),request.POST.get('xml', None)))


    # This bot that doesn't care
    return HttpResponse("")


def hipay_shop_logo(request):
    # FIXME: Think white label too
    pass

@login_required
def home(request):
    # Used for the apps to transparently force oauth login of there
    # users to webfinance-fo, without it the new users unknown to
    # webfinance will fail api call.
    if request.GET.get('nextapp', False):
        return redirect(request.GET.get('nextapp'))

    try:
        current_user = Users.objects.get(email=request.user.email)
        # We keep a loose coupling with native Users database from the backend
        customer_list = current_user.clients_set.all()
    except Users.DoesNotExist:
        customer_list = None
    if  customer_list and customer_list.count() == 1:
        return redirect(reverse('list_invoices', kwargs={'customer_id':customer_list[0].pk}))
    return redirect(reverse('list_companies'))


@login_required
def renew_subscription(request, subscription_id):
    current_user = Users.objects.get(email=request.user.email)
    subscriptions = [c.subscription_set.all() for c in current_user.clients_set.all()]
    if not subscriptions:
        raise Http404

    qs  = reduce(operator.or_, subscriptions)
    subscription = get_object_or_404(qs, pk=subscription_id)
    tr = SubscriptionTransaction(subscription=subscription)
    tr.save()

    # We create an invoice for the first payment and the subsequent payments are
    # created by a celery task using the first=False to get the payment details
    next_invoice = subscription.invoices_set.create(client=subscription.client,
                                            invoice_num=subscription.ref_contrat,
                                            period=subscription.period,
                                            )
    try:
        sr = subscription.subscriptionrow_set.get(first=False)
    except Subscription.DoesNotExist:
        raise ValueError("Malformed subscription %s, if this is a legacy subscription it should have been fixed by now"%subscription.pk)
    next_invoice.invoicerows_set.create(description=sr.description,
                                         qty=sr.qty,
                                         df_price=sr.price_excl_vat)
    response = hipay.simplepayment(next_invoice, sender_host=request.get_host(), secure=request.is_secure(), internal_transid=tr.pk)

    if response['status'] == 'Accepted':
        tr.redirect_url = response['message']
        tr.save()

        return redirect(response['message'])
    else:
        raise ValueError(response)
