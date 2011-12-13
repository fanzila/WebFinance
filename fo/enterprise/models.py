#!/usr/bin/env python
# -*- coding: utf-8 -*-
#Copyright (C) 2011 ISVTEC SARL
#$Id$
from __future__ import with_statement
__author__ = "Ousmane Wilane ♟ <ousmane@wilane.org>"
__date__   = "Thu Nov 10 14:20:07 2011"

from django.db import models
from django.utils.translation import ugettext_lazy as _
from django_countries import CountryField
from django.template import Context
from django.core.urlresolvers import reverse
from django.conf import settings
from django.core.mail import send_mail
import reversion
from uuid import uuid4
from fo.libs.sso import CYBSSOService, CYBSSO_URL
from fo.libs.utils import fo_get_template, select_template
import logging
logger = logging.getLogger('wf')

import hmac
try:
    from hashlib import sha1
except ImportError:
    import sha
    sha1 = sha.sha

class Users(models.Model):
    id_user = models.AutoField(primary_key=True)
    #customer = models.ManyToManyField('Clients', through='Clients2Users')
    last_name = models.CharField(max_length=300, blank=True)
    first_name = models.CharField(max_length=300, blank=True)
    login = models.CharField(unique=True, max_length=255)
    password = models.CharField(max_length=300, blank=True)
    email = models.EmailField(unique=True, max_length=255, blank=True)
    disabled = models.NullBooleanField(default=False)
    last_login = models.DateTimeField(null=True, blank=True)
    creation_date = models.DateTimeField(null=True, blank=True)
    role = models.CharField(max_length=192, blank=True)
    modification_date = models.DateTimeField(null=True, blank=True)
    prefs = models.TextField(blank=True)

    class Meta:
        verbose_name = _('User')
        verbose_name_plural = _('Users')
        db_table = u'webfinance_users'
        
    def __unicode__(self):
        return u"%s | %s" % (
            unicode(self.get_full_name()),
            unicode(self.login))
    
    def get_full_name(self):
        cybsso = CYBSSOService(CYBSSO_URL)
        userinfo = cybsso.UserGetInfo(self.email)
        return u"%s %s" %(userinfo.get('firstname', None), userinfo.get('lastname', None))


class Clients(models.Model):
    id_client = models.AutoField(primary_key=True)
    users = models.ManyToManyField('Users', through='Clients2Users')
    nom = models.CharField(_('Company name'), unique=True, max_length=255)
    cp = models.CharField(_('Postal code'), max_length=10)
    ville = models.CharField(_('City'), max_length=100)
    addr1 = models.CharField(_('Address 1'), max_length=265)    
    addr2 = models.CharField(_('Address 2'), max_length=265, blank=True)
    addr3 = models.CharField(_('Address 3'), max_length=265, blank=True)
    pays = CountryField(_('Country'), max_length=50)
    tel = models.CharField(_('Phone'), max_length=15, blank=True)
    fax = models.CharField(_('Fax'), max_length=200, blank=True)
    web = models.CharField(_('Website'), max_length=100, blank=True)
    vat_number = models.CharField(_('VAT number'), max_length=40, blank=True, null=True)
    has_unpaid = models.NullBooleanField(_('Has unpaid'), default=False)
    ca_total_ht = models.DecimalField(_('Total turnover'), null=True, max_digits=22, decimal_places=4, blank=True)
    ca_total_ht_year = models.DecimalField(_('Total pre-taxed turnover'), null=True, max_digits=22, decimal_places=4, blank=True)
    has_devis = models.NullBooleanField(_('Has quote?'), default=False)
    email = models.EmailField(_('Email'),max_length=255, blank=True)
    siren = models.CharField(_('Siren'), max_length=50, blank=True)
    total_du_ht = models.DecimalField(_('Amount of duty'), null=True, max_digits=22, decimal_places=4, blank=True)
    id_company_type = models.ForeignKey('CompanyTypes', verbose_name=_('Company type'), db_column='id_company_type')
    # This is the user who created this I guess
    id_user = models.ForeignKey(Users, verbose_name=_('User'), related_name='creator', db_column='id_user') #models.IntegerField()
    password = models.CharField(_('Password'), max_length=300, blank=True)

    class Meta:
        verbose_name = _('Customer')
        verbose_name_plural = _('Customers')
        db_table = u'webfinance_clients'
        
    def __unicode__(self):
        return u"%s" % (
            unicode(self.nom))

# M2M Jonction table ... This doesn't showup nowhere
class Clients2Users(models.Model):
    id = models.IntegerField(primary_key=True)
    client = models.ForeignKey(Clients,  db_column='id_client')
    user = models.ForeignKey(Users, db_column='id_user')
    role = models.ForeignKey('Roles', blank=True, null=True)

    class Meta:
        verbose_name = _('Client/User')
        verbose_name_plural = _('Client/User')
        db_table = u'webfinance_clients2users'

    def __unicode__(self):
        return u"%s | %s" % (
            unicode(self.client),
            unicode(self.user))


class CompanyTypes(models.Model):
    id_company_type = models.IntegerField(primary_key=True)
    nom = models.CharField(max_length=765, blank=True)

    class Meta:
        verbose_name = _('Company type')
        verbose_name_plural = _('Company types')
        db_table = u'webfinance_company_types'

    def __unicode__(self):
        return u"%s | %s" % (
            unicode(self.id_company_type),
            unicode(self.nom))

class Userlog(models.Model):
    id_userlog = models.IntegerField(primary_key=True)
    log = models.TextField(blank=True)
    date = models.DateTimeField(null=True, blank=True)
    id_user = models.ForeignKey(Users, db_column='id_user') #models.IntegerField(null=True, blank=True)
    id_facture = models.IntegerField(null=True, blank=True)
    id_client = models.IntegerField(null=True, blank=True)

    class Meta:
        verbose_name = _('User log')
        verbose_name_plural = _('User logs')
        db_table = u'webfinance_userlog'

    def __unicode__(self):
        return u"%s | %s | %s" % (
            unicode(self.log),
            unicode(self.date),
            unicode(self.id_facture))

        

class Personne(models.Model):
    id_personne = models.IntegerField(primary_key=True)
    nom = models.CharField(max_length=300, blank=True)
    prenom = models.CharField(max_length=300, blank=True)
    date_created = models.DateTimeField(null=True, blank=True)
    entreprise = models.CharField(max_length=90, blank=True)
    fonction = models.CharField(max_length=90, blank=True)
    tel = models.CharField(max_length=45, blank=True)
    tel_perso = models.CharField(max_length=45, blank=True)
    mobile = models.CharField(max_length=45, blank=True)
    fax = models.CharField(max_length=45, blank=True)
    email = models.CharField(max_length=765, blank=True)
    adresse1 = models.CharField(max_length=765, blank=True)
    ville = models.CharField(max_length=765, blank=True)
    cp = models.CharField(max_length=30, blank=True)
    digicode = models.CharField(max_length=30, blank=True)
    station_metro = models.CharField(max_length=30, blank=True)
    date_anniversaire = models.CharField(max_length=30, blank=True)
    note = models.TextField(blank=True)
    client = models.ForeignKey(Clients, db_column='client')

    class Meta:
        verbose_name = _('Person')
        verbose_name_plural = _('Persons')
        db_table = u'webfinance_personne'

    def __unicode__(self):
        return u"%s | %s | %s" % (
            unicode(self.nom),
            unicode(self.prenom),
            unicode(self.entreprise))


        
class Roles(models.Model):
    id_role = models.IntegerField(primary_key=True)
    name = models.CharField(unique=True, max_length=60)
    description = models.TextField(blank=True)

    class Meta:
        verbose_name = _('Role')
        verbose_name_plural = _('Roles')
        db_table = u'webfinance_roles'

    def __unicode__(self):
        return u"%s | %s " % (
            unicode(self.name),
            unicode(self.description))


class Invitation(models.Model):
    token = models.CharField(max_length=255, editable=False)
    revocation_token = models.CharField(max_length=255, editable=False)
    email = models.EmailField(_('Email address'))
    first_name = models.CharField(_('First name'), max_length=255)
    last_name = models.CharField(_('Last name'), max_length=255)
    company = models.ForeignKey(Clients)
    guest = models.ForeignKey(Users, editable=False)
    accepted = models.BooleanField(default=False, editable=False)
    revoked = models.BooleanField(default=False, editable=False)
    acceptation_date = models.DateTimeField(blank=True, null=True, editable=False)
    revocation_date = models.DateTimeField(blank=True, null=True, editable=False)

    class Meta:
        unique_together = ('email', 'company', 'guest')

    def __unicode__(self):
        return u"%s | %s %s" % (
            unicode(self.token),
            unicode(self.first_name),
            unicode(self.last_name))

    def save(self, *args, **kwargs):
        if not self.token:
            new_uuid, revocation_uuid = uuid4(), uuid4()
            self.token = hmac.new(str(new_uuid), digestmod=sha1).hexdigest()
            self.revocation_token = hmac.new(str(revocation_uuid), digestmod=sha1).hexdigest()
        super(Invitation, self).save(*args, **kwargs)

    def get_full_name(self):
        return u"%s %s" %(self.first_name, self.last_name)

    def send_invitation(self, host):
        subject = _("Invitation to join %(name)s from %(full_name)s" %{'name':self.company.nom,
                                                                       'full_name':self.guest.get_full_name()})
        message_template = fo_get_template(host,'enterprise/emails/invitation.txt', True)
        message_context = Context({'recipient_name': self.get_full_name(),
                                   'sender_name': self.guest.get_full_name(),
                                   'company': self.company.nom,
                                   'accept_url':"%s%s" %(host, reverse('accept_invitation',
                                                                            kwargs={'token':self.token})),
                                   'EMAIL_BASE_TEMPLATE':select_template(fo_get_template(host,settings.EMAIL_BASE_TEMPLATE)),
                                   'ADDRESS_TEMPLATE':select_template(fo_get_template(host,settings.COMPANY_ADDRESS)),
                                   })
        message = message_template.render(message_context)
        send_mail(subject, message, settings.DEFAULT_FROM_EMAIL, [self.email])

    def send_acceptation(self, host):
        subject = _("Invitation to join %(name)s accepted by  %(full_name)s" %{'name':self.company.nom,
                                                                               'full_name':self.guest.get_full_name()})
        message_template = fo_get_template(host,'enterprise/emails/acceptation.txt', True)
        message_context = Context({'invited': self.get_full_name(),
                                   'guest': self.guest.get_full_name(),
                                   'company': self.company.nom,
                                   'revocation_url':"%s%s" %(host, reverse('revoke_invitation',
                                                                         kwargs={'token':self.revocation_token})),
                                   'EMAIL_BASE_TEMPLATE':select_template(fo_get_template(host, settings.EMAIL_BASE_TEMPLATE)),
                                   'ADDRESS_TEMPLATE':select_template(fo_get_template(host, settings.COMPANY_ADDRESS)),
                                  })
        message = message_template.render(message_context)
        send_mail(subject, message, settings.DEFAULT_FROM_EMAIL, [self.guest.email])


try:
    reversion.register(Clients)
except reversion.revisions.RegistrationError:
    pass
