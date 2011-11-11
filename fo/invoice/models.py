#!/usr/bin/env python
# -*- coding: utf-8 -*-
#Copyright (C) 2011 ISVTEC SARL
#$Id$

__author__ = "Ousmane Wilane ♟ <ousmane@wilane.org>"
__date__   = "Thu Nov 10 16:59:10 2011"



from django.utils.translation import ugettext_lazy as _
from django.db import models

from fo.enterprise.models import Clients

class InvoiceRows(models.Model):
    id_facture_ligne = models.IntegerField(primary_key=True)
    id_facture = models.ForeignKey('Invoices', db_column='id_facture')
    description = models.TextField(blank=True)
    qtt = models.DecimalField(null=True, max_digits=7, decimal_places=2, blank=True)
    ordre = models.IntegerField(null=True, blank=True)
    prix_ht = models.DecimalField(null=True, max_digits=22, decimal_places=5, blank=True)
    class Meta:
        verbose_name = _('Invoice item')
        verbose_name_plural = _('Invoice items')
        db_table = u'webfinance_invoice_rows'
        

    def __unicode__(self):
        return u"%s | %s | %s | %s" % (
            unicode(self.id_facture_ligne),
            unicode(self.description),
            unicode(self.qtt),
            unicode(self.prix_ht))



class Invoices(models.Model):
    id_facture = models.IntegerField(primary_key=True)
    client = models.ForeignKey(Clients, db_column='id_client')
    date_created = models.DateTimeField(null=True, blank=True)
    date_generated = models.DateTimeField(null=True, blank=True)
    date_sent = models.DateTimeField(null=True, blank=True)
    date_paiement = models.DateTimeField(null=True, blank=True)
    is_paye = models.IntegerField(null=True, blank=True)
    num_facture = models.CharField(unique=True, max_length=30, blank=True)
    type_paiement = models.CharField(max_length=765, blank=True)
    ref_contrat = models.CharField(max_length=765, blank=True)
    extra_top = models.TextField(blank=True)
    facture_file = models.CharField(max_length=765, blank=True)
    accompte = models.DecimalField(null=True, max_digits=12, decimal_places=4, blank=True)
    extra_bottom = models.TextField(blank=True)
    date_facture = models.DateTimeField(null=True, blank=True)
    type_doc = models.CharField(max_length=27, blank=True)
    commentaire = models.TextField(blank=True)
    id_type_presta = models.IntegerField(null=True, blank=True)
    id_compte = models.IntegerField()
    is_envoye = models.IntegerField(null=True, blank=True)
    period = models.CharField(max_length=27, blank=True)
    periodic_next_deadline = models.DateField(null=True, blank=True)
    delivery = models.CharField(max_length=18, blank=True)
    payment_method = models.CharField(max_length=39, blank=True)
    tax = models.DecimalField(max_digits=7, decimal_places=2)
    exchange_rate = models.DecimalField(max_digits=10, decimal_places=2)

    class Meta:
        verbose_name = _('Invoice')
        verbose_name_plural = _('Invoices')
        db_table = u'webfinance_invoices'
        
    def __unicode__(self):
        return u"%s | %s | %s | %s" % (
            unicode(self.num_facture),
            unicode(self.client),
            unicode(self.id_type_presta),
            unicode(self.period))



class Paybox(models.Model):
    id_paybox = models.IntegerField(primary_key=True)
    id_invoice = models.IntegerField()
    email = models.CharField(max_length=765, blank=True)
    reference = models.CharField(unique=True, max_length=255)
    state = models.CharField(max_length=21)
    amount = models.DecimalField(max_digits=16, decimal_places=2)
    currency = models.IntegerField()
    autorisation = models.CharField(max_length=192)
    transaction_id = models.CharField(max_length=192)
    payment_type = models.CharField(max_length=192)
    card_type = models.CharField(max_length=192)
    transaction_sole_id = models.CharField(max_length=192)
    error_code = models.CharField(max_length=192)
    date = models.DateTimeField(null=True, blank=True)


    class Meta:
        verbose_name = _('PayBox')
        verbose_name_plural = _('PayBox')
        db_table = u'webfinance_paybox'
        
    def __unicode__(self):
        return u"%s | %s | %s | %s" % (
            unicode(self.email),
            unicode(self.reference),
            unicode(self.amount),
            unicode(self.state))


        
class TransactionInvoice(models.Model):
    id_transaction = models.ForeignKey('Transactions', db_column='id_transaction')
    id_invoice = models.ForeignKey('Invoices', db_column='id_invoice')
    date_update = models.DateTimeField()

    class Meta:
        verbose_name = _("Transaction's invoice")
        verbose_name_plural = _("Transaction's invoices")
        db_table = u'webfinance_transaction_invoice'
        
    def __unicode__(self):
        return u"%s | %s " % (
            unicode(self.id_invoice),
            unicode(self.date_update))


class Transactions(models.Model):
    id = models.IntegerField(primary_key=True)
    id_account = models.IntegerField()
    id_category = models.IntegerField()
    text = models.CharField(max_length=765)
    amount = models.DecimalField(max_digits=16, decimal_places=2)
    exchange_rate = models.DecimalField(max_digits=10, decimal_places=2)
    type = models.CharField(max_length=27, blank=True)
    document = models.CharField(max_length=384, blank=True)
    date = models.DateField()
    date_update = models.DateTimeField()
    comment = models.TextField(blank=True)
    file = models.TextField(blank=True)
    file_type = models.CharField(max_length=75, blank=True)
    file_name = models.CharField(max_length=150, blank=True)
    lettrage = models.IntegerField(null=True, blank=True)
    id_invoice = models.ManyToManyField('Invoices', through='TransactionInvoice')

    class Meta:
        verbose_name = _('Transaction')
        verbose_name_plural = _('Transactions')
        db_table = u'webfinance_transactions'
        
    def __unicode__(self):
        return u"%s | %s " % (
            unicode(self.id_account),
            unicode(self.date_amount))


class TypePresta(models.Model):
    id_type_presta = models.IntegerField(primary_key=True)
    nom = models.CharField(unique=True, max_length=255, blank=True)

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
    nom = models.CharField(max_length=765, blank=True)
    taux = models.DecimalField(null=True, max_digits=7, decimal_places=3, blank=True)

    class Meta:
        verbose_name = _('VAT type')
        verbose_name_plural = _('VAT types')
        db_table = u'webfinance_type_tva'

    def __unicode__(self):
        return u"%s | %s " % (
            unicode(self.nom),
            unicode(self.taux))


