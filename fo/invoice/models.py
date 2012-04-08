#!/usr/bin/env python
# -*- coding: utf-8 -*-
#Copyright (C) 2011 ISVTEC SARL
#$Id$

__author__ = "Ousmane Wilane ♟ <ousmane@wilane.org>"
__date__   = "Thu Nov 10 16:59:10 2011"

import urllib2
from urllib import urlencode
from datetime import datetime
from django.utils.translation import ugettext_lazy as _
from django.db import models
from enterprise.models import Clients
from django.core import serializers
from uuid import uuid4
from libs.utils import fo_get_template, select_template
from django.template import Context
from django.core.urlresolvers import reverse
from django.conf import settings
from django.core.mail import send_mail
from dateutil.relativedelta import relativedelta
import logging
logger = logging.getLogger('isvtec')

TRANSACTION_STATUS = ('cancel', 'ok', 'nook', 'pending')
PERIODS = ('monthly', 'quarterly', 'yearly')
DELIVERY_TYPES = ('email', 'postal')
PAYMENT_METHODS = ('unknown', 'direct_debit', 'check', 'wire_transfer')
DOC_TYPES = ('quote','invoice')
SUBSCRIPTION_STATUS = ('running', 'canceled', 'suspended', 'expired', 'setup')
DEFAULT_VAT = '19.60'
FACTORS = {'monthly':1,
           'quarterly': 3,
           'yearly': 12}
UPDATE_TYPES = ['setup', 'upgrade', 'renewal']

class InvoiceRows(models.Model):
    id = models.AutoField(primary_key=True, db_column='id_facture_ligne')
    invoice = models.ForeignKey('Invoices', db_column='id_facture')
    description = models.TextField(blank=True)
    qty = models.DecimalField(null=True, max_digits=7, decimal_places=2, blank=True, db_column="qtt")
    order = models.IntegerField(null=True, blank=True, db_column="ordre")
    df_price = models.DecimalField(null=True, max_digits=22, decimal_places=5, blank=True, db_column="prix_ht")
    class Meta:
        verbose_name = _('Invoice item')
        verbose_name_plural = _('Invoice items')
        db_table = u'webfinance_invoice_rows'


    def __unicode__(self):
        return u"%s | %s | %s | %s" % (
            unicode(self.id),
            unicode(self.description),
            unicode(self.qty),
            unicode(self.dt_price))

class SubscriptionRow(models.Model):
    subscription = models.ForeignKey('Subscription')
    description = models.CharField(max_length=1024)
    qty = models.DecimalField(null=True, max_digits=5, decimal_places=2, blank=True)
    price_excl_vat = models.DecimalField(null=True, max_digits=20, decimal_places=5, blank=True)
    first = models.BooleanField(default=False)

    class Meta:
        verbose_name = _('Subscription row')
        verbose_name_plural = _('Subscription rows')
        db_table = u'webfinance_subscription_rows'

    def __unicode__(self):
        return u"%s | %s | %s | %s" % (
            unicode(self.subscription),
            unicode(self.description),
            unicode(self.qty),
            unicode(self.price_excl_vat),)

class Subscription(models.Model):
    client = models.ForeignKey(Clients)
    ref_contrat = models.CharField(max_length=255)
    period = models.CharField(max_length=16, choices=zip(PERIODS, filter(_, PERIODS)), default='monthly')
    periodic_next_deadline = models.DateField()
    delivery = models.CharField(max_length=16, choices=zip(DELIVERY_TYPES, filter(_, DELIVERY_TYPES)), default='email')
    payment_method = models.CharField(max_length=16, choices=zip(PAYMENT_METHODS, filter(_, PAYMENT_METHODS)), default='unknown')
    tax = models.DecimalField(max_digits=5, decimal_places=2,default=DEFAULT_VAT)
    type_doc = models.CharField(max_length=16, choices=zip(DOC_TYPES, filter(_, DOC_TYPES)), default='invoice')
    info = models.TextField(null=True, blank=True)
    service_name = models.TextField(null=True, blank=True) # This is updated by
                                                           # the app once the
                                                           # service is
                                                           # completely known (servername or ipaddres for example)
    status = models.CharField(max_length=16, choices=zip(SUBSCRIPTION_STATUS, SUBSCRIPTION_STATUS), default='setup')
    paid = models.BooleanField(default=False) # The last payment date is know to
                                              # the invoices_set of the instance
                                              # but will reproduce it here to
                                              # avoid expensive computation
    payment_date = models.DateTimeField(blank=True, null=True)
    reminder_sent_date = models.DateTimeField(blank=True, null=True)
    expiration_date = models.DateTimeField(blank=True, null=True)
    status_url = models.URLField(blank=True, null=True)
    order = models.ForeignKey('order', blank=True, null=True)

    class Meta:
        verbose_name = _('Subscription')
        verbose_name_plural = _('Subscriptions')
        db_table = u'webfinance_subscription'

    def set_expiration_date(self):
        """This is just to be safe, but will let the apps update the fields
        ... for e.g if they need time to activate the service"""
        # This is not the first payment
        if self.expiration_date:
            self.expiration_date += relativedelta(months=FACTORS.get(self.period))
        else:
            # First payment
            if self.payment_date:
                # The app might need to reset this and it's likely to do so
                self.expiration_date = self.payment_date + relativedelta(months=FACTORS.get(self.period))
            else:
                # This is a problem and need to be reset by the app
                self.expiration_date = datetime.now() + relativedelta(months=FACTORS.get(self.period))
        self.save()

    def __unicode__(self):
        return u"%s | %s | %s " % (
            unicode(self.ref_contrat),
            unicode(self.delivery),
            unicode(self.tax))

    def send_reminder(self, host=None):
        """ This is responsible for sending reminders for expiring services """
        host = host or settings.DEFAULT_HOST
        subject = _("Service subscriptions status on ISVTEC : %(name)s" %{'name':self.client.name})
        message_template = fo_get_template(host,'invoice/emails/payment_reminder.txt', True)
        message_context = Context({'recipient_name': self.client.name,
                                   'expiration_date': self.expiration_date,
                                   'info': self.info,
                                   'service_name': self.service_name,
                                   'sender_name': 'Service Client ISVTEC', # FIXME: change this for white label
                                   'company': self.client.name,
                                   'renew_url':"%s%s" %(settings.WEB_HOST, reverse('renew_subscription',
                                                                                   kwargs={'subscription_id':self.pk})),
                                   'EMAIL_BASE_TEMPLATE':select_template(fo_get_template(host,settings.EMAIL_BASE_TEMPLATE)),
                                   'ADDRESS_TEMPLATE':select_template(fo_get_template(host,settings.COMPANY_ADDRESS)),
                                   })
        message = message_template.render(message_context)
        send_mail(subject, message, settings.DEFAULT_FROM_EMAIL, [dest.email for dest in self.client.users.all()] + [self.client.email])

    def send_subscription_notice(self, host=None):
        host = host or settings.DEFAULT_HOST
        subject = _("Subscription ISVTEC : %(name)s" %{'name':self.client.name})
        message_template = fo_get_template(host,'invoice/emails/subscription_notice.txt', True)
        message_context = Context({'recipient_name': self.client.name,
                                   #'subscription': self,
                                   'info': self.info,
                                   'sender_name': _("ISVTEC Customer service"), # FIXME: change this for white label
                                   'company': self.client.name,
                                   'EMAIL_BASE_TEMPLATE':select_template(fo_get_template(host,settings.EMAIL_BASE_TEMPLATE)),
                                   'ADDRESS_TEMPLATE':select_template(fo_get_template(host,settings.COMPANY_ADDRESS)),
                                   })
        message = message_template.render(message_context)
        send_mail(subject, message, settings.DEFAULT_FROM_EMAIL, [dest.email for dest in self.client.users.all()] + [self.client.email])



class Invoices(models.Model):
    id = models.AutoField(primary_key=True, db_column='id_facture')
    client = models.ForeignKey(Clients, db_column='id_client')
    date_created = models.DateTimeField(null=True, blank=True, auto_now_add=True)
    date_generated = models.DateTimeField(null=True, blank=True)
    date_sent = models.DateTimeField(null=True, blank=True)
    payment_date = models.DateTimeField(null=True, blank=True, db_column="date_paiement")
    paid = models.NullBooleanField(default=False, db_column="is_paye")
    invoice_num = models.CharField(unique=True, max_length=30, blank=True, db_column="num_facture")
    payment_type = models.CharField(max_length=765, blank=True, db_column="type_paiement")
    ref_contract = models.CharField(max_length=765, blank=True, db_column="ref_contrat")
    extra_top = models.TextField(blank=True)
    sub_invoice = models.CharField(max_length=765, blank=True, db_column="facture_file")
    down_payment = models.DecimalField(null=True, max_digits=12, decimal_places=4, blank=True, default='0', db_column="accompte")
    extra_bottom = models.TextField(blank=True)
    invoice_date = models.DateTimeField(null=True, blank=True, db_column="date_facture")
    type_doc = models.CharField(max_length=27, blank=True, default='facture')
    comment = models.TextField(blank=True, db_column="commentaire")
    service_type = models.IntegerField(null=True, blank=True, db_column="id_type_presta")
    account = models.IntegerField(default=30, db_column="id_compte")
    sent = models.IntegerField(null=True, blank=True, db_column="is_envoye")
    period = models.CharField(max_length=27, blank=True, default='monthly') #FIXME: remove me
    periodic_next_deadline = models.DateField(null=True, blank=True) #FIXME: remove me
    delivery = models.CharField(max_length=18, blank=True, default='email') #FIXME: remove me
    payment_method = models.CharField(max_length=39, blank=True, default='unknown') #FIXME: remove me
    tax = models.DecimalField(default=DEFAULT_VAT, max_digits=7, decimal_places=2)
    exchange_rate = models.DecimalField(default='1.00', max_digits=10, decimal_places=2)
    subscription = models.ForeignKey('Subscription', blank=True, null=True, related_name="sub_invoices")
    update_type = models.CharField(max_length=18, blank=True, choices=zip(UPDATE_TYPES, UPDATE_TYPES), default='setup')
    order = models.ForeignKey('order', blank=True, null=True)

    # Ovveride the status url of the global subscription if this is defined
    # (simple way to propage special orders)
    status_url = models.URLField(blank=True, null=True)

    @property
    def id_facture_id(self):
        """Heu yeah awry ... tastypie search this thing somehow with a rather
        fancy exception, thanks Python"""
        return self.invoice_num
    @property
    def info(self):
        if self.subscription:
            return self.subscription.service_name or self.subscription.info
        return "| ".join([r.description for r in self.invoicerows_set.all()])

    class Meta:
        verbose_name = _('Invoice')
        verbose_name_plural = _('Invoices')
        db_table = u'webfinance_invoices'

    def __unicode__(self):
        return u"%s | %s | %s " % (
            unicode(self.invoice_num),
            unicode(self.service_type),
            unicode(self.period))

class TypePresta(models.Model):
    id_type_presta = models.IntegerField(primary_key=True)
    name = models.CharField(unique=True, max_length=255, blank=True, db_column="nom")

    class Meta:
        verbose_name = _('Service type')
        verbose_name_plural = _('Service types')
        db_table = u'webfinance_type_presta'

    def __unicode__(self):
        return u"%s | %s " % (
            unicode(self.id_type_presta),
            unicode(self.nom))

class Suivi(models.Model):
    id_suivi = models.IntegerField(primary_key=True)
    type_suivi = models.IntegerField(null=True, blank=True)
    id_objet = models.IntegerField()
    message = models.TextField(blank=True)
    date_added = models.DateTimeField(null=True, blank=True)
    date_modified = models.DateTimeField(null=True, blank=True)
    added_by = models.IntegerField(null=True, blank=True)
    rappel = models.DateTimeField(null=True, blank=True)
    done = models.IntegerField(null=True, blank=True)
    done_date = models.DateTimeField(null=True, blank=True)

    class Meta:
        verbose_name = _('Followup')
        verbose_name_plural = _('Followups')
        db_table = u'webfinance_suivi'

    def __unicode__(self):
        return u"%s | %s " % (
            unicode(self.type_suivi),
            unicode(self.rappel))


class TypeSuivi(models.Model):
    id_type_suivi = models.IntegerField(primary_key=True)
    name = models.CharField(max_length=600, blank=True)
    selectable = models.IntegerField(null=True, blank=True)

    class Meta:
        verbose_name = _('Followup type')
        verbose_name_plural = _('Followup types')
        db_table = u'webfinance_type_suivi'

    def __unicode__(self):
        return u"%s | %s " % (
            unicode(self.name),
            unicode(self.selectable))


class TypeTva(models.Model):
    id_type_tva = models.IntegerField(primary_key=True)
    name = models.CharField(max_length=765, blank=True, db_column="nom")
    rate = models.DecimalField(null=True, max_digits=7, decimal_places=3, blank=True, db_column="taux")

    class Meta:
        verbose_name = _('VAT type')
        verbose_name_plural = _('VAT types')
        db_table = u'webfinance_type_tva'

    def __unicode__(self):
        return u"%s | %s " % (
            unicode(self.nom),
            unicode(self.taux))


class InvoiceTransaction(models.Model):
    invoice = models.ForeignKey(Invoices)
    status = models.CharField(max_length=255,null=True, blank=True)
    emailClient = models.EmailField(null=True, blank=True)
    date = models.DateField(null=True, blank=True)
    operation = models.CharField(max_length=255, null=True, blank=True)
    transid =  models.CharField(max_length=255, null=True, blank=True)
    merchantDatas = models.CharField(max_length=255, null=True, blank=True)
    origCurrency = models.CharField(max_length=255, default='EUR')
    origAmount  = models.CharField(max_length=255, null=True, blank=True)
    idForMerchant = models.CharField(max_length=255, null=True, blank=True)
    refProduct = models.CharField(max_length=255, null=True, blank=True)
    time = models.TimeField(null=True, blank=True)
    subscriptionId = models.CharField(max_length=255, null=True, blank=True)
    not_tempered_with = models.BooleanField(default=False, editable=False)
    url_ack = models.URLField(null=True, blank=True)

    # HiPay redirect URL for debugging
    redirect_url = models.URLField(null=True, blank=True)
    first_status = models.CharField(max_length=16, choices=zip(TRANSACTION_STATUS, TRANSACTION_STATUS), default='pending')

    def __unicode__(self):
        return u"%s | %s  | %s | %s" % (
            unicode(self.status),
            unicode(self.operation),
            unicode(self.transid),
            unicode(self.refProduct))

    #FIXME: Refactor these send emails
    def send_invoice_notice(self, host=None):
        """This is sent to signal that an invoice have been made available for
        immediate payment. This is triggered by other apps sending subscriptions
        or payments for there services. The payment starts from WebFinance-FO
        and the payment gateway is not hit untill the user log into
        WebFinance-FO"""
        host = host or settings.DEFAULT_HOST
        subject = _("Invoice ISVTEC : %(name)s" %{'name':self.invoice.client.name})
        message_template = fo_get_template(host,'invoice/emails/invoice_notice.txt', True)
        message_context = Context({'recipient_name': self.invoice.client.name,
                                   'transaction': self,
                                   'info': self.invoice.info,
                                   'sender_name': _("ISVTEC Customer service"), # FIXME: change this for white label
                                   'company': self.invoice.client.name,
                                   'payment_url': self.redirect_url,
                                   'invoice_url': "%s%s" %(settings.WEB_HOST, reverse('hipay_invoice',
                                                                            kwargs={'invoice_id':self.invoice.pk})),
                                   'EMAIL_BASE_TEMPLATE':select_template(fo_get_template(host,settings.EMAIL_BASE_TEMPLATE)),
                                   'ADDRESS_TEMPLATE':select_template(fo_get_template(host,settings.COMPANY_ADDRESS)),
                                   })
        message = message_template.render(message_context)
        send_mail(subject, message, settings.DEFAULT_FROM_EMAIL, [dest.email for dest in self.invoice.client.users.all()] + [self.invoice.client.email])

    def payment_received_notice(self, host=None):
        """This is sent to signal that an invoice have been paid
           This is responsible for sending reminders for expiring services """
        host = host or settings.DEFAULT_HOST
        subject = _("Invoice ISVTEC (Payment Received) : %(name)s" %{'name':self.invoice.client.name})
        message_template = fo_get_template(host,'invoice/emails/invoice_received_notice.txt', True)
        message_context = Context({'recipient_name': self.invoice.client.name,
                                   'transaction': self,
                                   'info': self.invoice.info,
                                   'sender_name': _("ISVTEC Customer service"), # FIXME: change this for white label
                                   'company': self.invoice.client.name,
                                   'invoice_url': "%s%s" %(settings.WEB_HOST, reverse('download_invoice',
                                                                            kwargs={'invoice_id':self.invoice.pk})),
                                   'EMAIL_BASE_TEMPLATE':select_template(fo_get_template(host,settings.EMAIL_BASE_TEMPLATE)),
                                   'ADDRESS_TEMPLATE':select_template(fo_get_template(host,settings.COMPANY_ADDRESS)),
                                   })
        message = message_template.render(message_context)
        send_mail(subject, message, settings.DEFAULT_FROM_EMAIL, [dest.email for dest in self.invoice.client.users.all()] + [self.invoice.client.email])

    def payment_failure_notice(self, host=None):
        """If a payment is canceled or if we get a failed capture funds, this
        will let you know
        This is responsible for sending reminders for expiring services """
        host = host or settings.DEFAULT_HOST
        subject = _("Invoice ISVTEC (Failure notice) : %(name)s" %{'name':self.invoice.client.name})
        message_template = fo_get_template(host,'invoice/emails/invoice_failure_notice.txt', True)
        message_context = Context({'recipient_name': self.invoice.client.name,
                                   'transaction': self,
                                   'info': self.invoice.info,
                                   'sender_name': _("ISVTEC Customer service"), # FIXME: change this for white label
                                   'company': self.invoice.client.name,
                                   'invoice_url': "%s%s" %(settings.WEB_HOST, reverse('hipay_invoice',
                                                                            kwargs={'invoice_id':self.invoice.pk})),
                                   'EMAIL_BASE_TEMPLATE':select_template(fo_get_template(host,settings.EMAIL_BASE_TEMPLATE)),
                                   'ADDRESS_TEMPLATE':select_template(fo_get_template(host,settings.COMPANY_ADDRESS)),
                                   })
        message = message_template.render(message_context)
        send_mail(subject, message, settings.DEFAULT_FROM_EMAIL, [dest.email for dest in self.invoice.client.users.all()] + [self.invoice.client.email])

    def save(self, *args, **kwargs):
        super(InvoiceTransaction, self).save(*args, **kwargs)
        if self.status == 'pending':
            self.send_invoice_notice()



class SubscriptionTransaction(models.Model):
    subscription = models.ForeignKey(Subscription)
    status = models.CharField(max_length=255,null=True, blank=True)
    emailClient = models.EmailField(null=True, blank=True)
    date = models.DateField(null=True, blank=True)
    operation = models.CharField(max_length=255, null=True, blank=True)
    transid =  models.CharField(max_length=255, null=True, blank=True)
    merchantDatas = models.CharField(max_length=255, null=True, blank=True)
    origCurrency = models.CharField(max_length=255, default='EUR')
    origAmount  = models.CharField(max_length=255, null=True, blank=True)
    idForMerchant = models.CharField(max_length=255, null=True, blank=True)
    refProduct = models.CharField(max_length=255, null=True, blank=True)
    time = models.TimeField(null=True, blank=True)
    subscriptionId = models.CharField(max_length=255, null=True, blank=True)
    not_tempered_with = models.BooleanField(default=False, editable=False)
    url_ack = models.URLField(null=True, blank=True)

    redirect_url = models.URLField(null=True, blank=True)
    first_status = models.CharField(max_length=16, choices=zip(TRANSACTION_STATUS, TRANSACTION_STATUS), default='pending')
    def __unicode__(self):
        return u"%s | %s | %s | %s | %s" % (
            unicode(self.status),
            unicode(self.operation),
            unicode(self.url_ack),
            unicode(self.transid),
            unicode(self.refProduct))

    def save(self, *args, **kwargs):
        if self.status and self.url_ack:
            data = serializers.serialize("json", SubscriptionTransaction.objects.filter(pk=self.id))
            opener = urllib2.build_opener()
            opener.addheaders = [("Content-Type", "text/json"),
                                 ("Content-Length", str(len(data))),
                                 ("User-Agent", u"ISVTEC -- PAYMENT GATEWAY")]
            urllib2.install_opener(opener)

            request = urllib2.Request(self.url_ack,urlencode({'payment':data}))
            try:
                response = opener.open(request)
                logger.info(u"Pinged back %s ... propagation, got '%s'" %(self.url_ack, response.read()))
            except Exception, e:
                logger.warn(u"Unable to ping back %s, we have an ack to propage: %s" %(self.url_ack, e))

        super(SubscriptionTransaction, self).save(*args, **kwargs)


class order(models.Model):
    # The app that requested this order, used for app keys (TBD) so the apps
    # could access there data even if a user isn't actualy logged in
    application_uri = models.URLField(blank=True, null=True)
    parent = models.ForeignKey('self', blank=True, null=True)
    client = models.ForeignKey(Clients)

    # This will allow to upgrade the order and update the subscription
    period = models.CharField(max_length=16,
                              choices=[(k, _(k)) for k in ('monthly', 'quarterly', 'yearly')],
                              default='monthly')
    order_type = models.CharField(max_length=32,
                              choices=[(k, _(k)) for k in ('invoice', 'subscription', 'refund')],
                              default='subscription')
    service_name = models.CharField(max_length=256)
    status_url = models.URLField(blank=True, null=True)
    checkout_url = models.URLField(blank=True, null=True)
    creation_date = models.DateTimeField(auto_now_add=True)
    modification_date = models.DateTimeField(auto_now=True)
    uuid = models.CharField(max_length=256, editable=False)

    def __unicode__(self):
        return u"%s | %s | %s" % (
            unicode(self.application_uri),
            unicode(self.service_name),
            unicode(self.period),
            )

    def save(self, *args, **kwargs):
        if not self.uuid:
            self.uuid = str(uuid4())
        if not self.checkout_url:
            self.checkout_url = reverse('checkout', kwargs={'order_id':self.uuid})
        super(order, self).save(*args, **kwargs)


class order_detail(models.Model):
    order = models.ForeignKey('order')
    description = models.CharField(max_length=1024)
    quantity = models.DecimalField(null=True, max_digits=5, decimal_places=2, blank=True)
    price = models.DecimalField(null=True, max_digits=20, decimal_places=5, blank=True)
    first = models.BooleanField(default=False)

    def __unicode__(self):
        return u"%s | %s | %s | %s" % (
            unicode(self.order),
            unicode(self.description),
            unicode(self.quantity),
            unicode(self.price),)
