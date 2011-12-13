#!/usr/bin/env python
# -*- coding: utf-8 -*-
#Copyright (C) 2011 ISVTEC SARL
#$Id$

__author__ = "Ousmane Wilane ♟ <ousmane@wilane.org>"
__date__   = "Thu Nov 10 16:59:10 2011"


import urllib2
from urllib import urlencode
from django.utils.translation import ugettext_lazy as _
from django.db import models
from fo.enterprise.models import Clients
from django.core import serializers
import logging
logger = logging.getLogger('wf')

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
    period = models.CharField(max_length=16, choices=[(k, _(k)) for k in ('monthly', 'quarterly', 'yearly')], default='monthly')
    periodic_next_deadline = models.DateField()
    delivery = models.CharField(max_length=16, choices=[(k, _(k)) for k in ('email', 'postal')], default='email')
    payment_method = models.CharField(max_length=16, choices=[(k, _(k)) for k in ('unknown', 'direct_debit', 'check', 'wire_transfer')], default='unknown')
    tax = models.DecimalField(max_digits=5, decimal_places=2,default='19.60')
    type_doc = models.CharField(max_length=16, choices=[(k, _(k)) for k in ('quote','invoice')], default='invoice') 
    
    class Meta:
        verbose_name = _('Subscription')
        verbose_name_plural = _('Subscriptions')
        db_table = u'webfinance_subscription'
        
    def __unicode__(self):
        return u"%s | %s | %s " % (
            unicode(self.ref_contrat),
            unicode(self.delivery),
            unicode(self.tax))

  
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
    tax = models.DecimalField(default='19.60', max_digits=7, decimal_places=2)
    exchange_rate = models.DecimalField(default='1.00', max_digits=10, decimal_places=2)


    @property
    def id_facture_id(self):
        """Heu yeah awry ... tastypie search this thing somehow with a rather
        fancy exception, thanks Python"""
        return self.invoice_num
    
    class Meta:
        verbose_name = _('Invoice')
        verbose_name_plural = _('Invoices')
        db_table = u'webfinance_invoices'
        
    def __unicode__(self):
        return u"%s | %s | %s " % (
            unicode(self.invoice_num),
            unicode(self.service_type),
            unicode(self.period))


# class Paybox(models.Model):
#     id_paybox = models.IntegerField(primary_key=True)
#     id_invoice = models.IntegerField()
#     email = models.CharField(max_length=765, blank=True)
#     reference = models.CharField(unique=True, max_length=255)
#     state = models.CharField(max_length=21)
#     amount = models.DecimalField(max_digits=16, decimal_places=2)
#     currency = models.IntegerField()
#     autorisation = models.CharField(max_length=192)
#     transaction_id = models.CharField(max_length=192)
#     payment_type = models.CharField(max_length=192)
#     card_type = models.CharField(max_length=192)
#     transaction_sole_id = models.CharField(max_length=192)
#     error_code = models.CharField(max_length=192)
#     date = models.DateTimeField(null=True, blank=True)


#     class Meta:
#         verbose_name = _('PayBox')
#         verbose_name_plural = _('PayBox')
#         db_table = u'webfinance_paybox'
        
#     def __unicode__(self):
#         return u"%s | %s | %s | %s" % (
#             unicode(self.email),
#             unicode(self.reference),
#             unicode(self.amount),
#             unicode(self.state))


        
# class TransactionInvoice(models.Model):
#     id_transaction = models.ForeignKey('Transactions', db_column='id_transaction')
#     id_invoice = models.ForeignKey('Invoices', db_column='id_invoice')
#     date_update = models.DateTimeField()

#     class Meta:
#         verbose_name = _("Transaction's invoice")
#         verbose_name_plural = _("Transaction's invoices")
#         db_table = u'webfinance_transaction_invoice'
        
#     def __unicode__(self):
#         return u"%s | %s " % (
#             unicode(self.id_invoice),
#             unicode(self.date_update))


# class Transactions(models.Model):
#     id = models.IntegerField(primary_key=True)
#     id_account = models.IntegerField()
#     id_category = models.IntegerField()
#     text = models.CharField(max_length=765)
#     amount = models.DecimalField(max_digits=16, decimal_places=2)
#     exchange_rate = models.DecimalField(max_digits=10, decimal_places=2)
#     type = models.CharField(max_length=27, blank=True)
#     document = models.CharField(max_length=384, blank=True)
#     date = models.DateField()
#     date_update = models.DateTimeField()
#     comment = models.TextField(blank=True)
#     file = models.TextField(blank=True)
#     file_type = models.CharField(max_length=75, blank=True)
#     file_name = models.CharField(max_length=150, blank=True)
#     lettrage = models.IntegerField(null=True, blank=True)
#     id_invoice = models.ManyToManyField('Invoices', through='TransactionInvoice')

#     class Meta:
#         verbose_name = _('Transaction')
#         verbose_name_plural = _('Transactions')
#         db_table = u'webfinance_transactions'
        
#     def __unicode__(self):
#         return u"%s | %s " % (
#             unicode(self.id_account),
#             unicode(self.date_amount))


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
    first_status = models.CharField(max_length=16, choices=[(k, k) for k in ('cancel', 'ok', 'nook', 'pending')], default='pending')

    def __unicode__(self):
        return u"%s | %s | %s | %s | %s" % (
            unicode(self.status),
            unicode(self.operation),
            unicode(self.url_ack),
            unicode(self.transid),
            unicode(self.refProduct))

    def save(self, *args, **kwargs):
        if self.status and self.url_ack: #This is only updated when the ACK comes in
            # Pinging back
            logger.info("Pinging %s for IPN from upstream" %self.url_ack)
            data = serializers.serialize("json", InvoiceTransaction.objects.filter(pk=self.id))
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

        super(InvoiceTransaction, self).save(*args, **kwargs)

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
    first_status = models.CharField(max_length=16, choices=[(k, k) for k in ('cancel', 'ok', 'nook', 'pending')], default='pending')

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

