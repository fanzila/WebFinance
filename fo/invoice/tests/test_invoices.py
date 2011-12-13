#!/usr/bin/env python
#-*- coding: utf-8 -*-
#Copyright (C) 2011 ISVTEC SARL
#$Id$
__author__ = "Ousmane Wilane ♟ <ousmane@wilane.org>"
__date__   = "Fri Nov 11 16:54:17 2011"

from django.test import TestCase
from django.core.urlresolvers import reverse
from django.utils.translation import ugettext_lazy as _
from invoice.models import Invoices, Subscription, InvoiceTransaction, SubscriptionTransaction
from enterprise.models import Clients, Clients2Users, Users
from django.http import HttpRequest
from django.contrib.auth.models import User
from tastypie.models import create_api_key
from urllib import urlencode
from django.conf import settings
import logging
logger = logging.getLogger('wf')
settings.TASTYPIE_FULL_DEBUG=True
settings.DEBUG=True
settings.AUTHENTICATION_BACKENDS = (
     'libs.auth.WFMockRemoteUserBackend',
)


try:
    import json
except ImportError:
    import simplejson as json
    
class InvoiceTest(TestCase):
    def setUp(self):
        # We need a ticket and an account for test to pass before we use
        # selenium and friends
        self.username = 'ousmane@wilane.org'
        self.ticket = ''

    def test_list_companies(self):
        url = reverse("list_companies")
        response = self.client.get(url)
        self.assertEqual(response.status_code, 302)
        self.client.login(username=self.username, ticket=self.ticket)
        response = self.client.get(url)
        self.assertEqual(response.status_code, 200)
        self.assertTemplateUsed(response, 'invoice/list_companies.html')
        self.assertContains(response, _("My companies"))
        self.client.logout()
        

    def test_list_invoice(self):
        url = reverse("list_invoices", kwargs={'customer_id':1})        
        response = self.client.get(url)
        self.assertEqual(response.status_code, 302)
        self.client.login(username=self.username, ticket=self.ticket)
        response = self.client.get(url)
        self.assertEqual(response.status_code, 200)
        self.assertTemplateUsed(response, 'invoice/list_invoices.html')
        self.assertContains(response, _("Invoices/Quotes for company"))
        self.client.logout()


    def test_list_invoice404(self):
        url = reverse("list_invoices", kwargs={'customer_id':123})        
        self.client.login(username=self.username, ticket=self.ticket)
        response = self.client.get(url)
        self.assertEqual(response.status_code, 404)
        self.client.logout()
        
    def test_detail_invoice(self):
        url = reverse("detail_invoice", kwargs={'invoice_id':1})
        response = self.client.get(url)
        self.assertEqual(response.status_code, 302)
        self.client.login(username=self.username, ticket=self.ticket)
        response = self.client.get(url)
        self.assertEqual(response.status_code, 200)
        self.assertTemplateUsed(response, 'invoice/detail_invoices.html')
        self.assertContains(response, "201111100")
        self.client.logout()
        
    def test_detail_subscription(self):
        url = reverse("detail_subscription", kwargs={'subscription_id':1})
        response = self.client.get(url)
        self.assertEqual(response.status_code, 302)
        self.client.login(username=self.username, ticket=self.ticket)
        response = self.client.get(url)
        self.assertEqual(response.status_code, 200)
        self.assertTemplateUsed(response, 'invoice/detail_subscriptions.html')
        self.assertContains(response, "0412201101")
        self.client.logout()

    def test_detail_invoice404(self):
        url = reverse("detail_invoice", kwargs={'invoice_id':123})
        response = self.client.get(url)
        self.client.login(username=self.username, ticket=self.ticket)
        response = self.client.get(url)
        self.assertEqual(response.status_code, 404)
        self.client.logout()
        
    def test_detail_subscription404(self):
        url = reverse("detail_subscription", kwargs={'subscription_id':123})
        response = self.client.get(url)
        self.client.login(username=self.username, ticket=self.ticket)
        response = self.client.get(url)
        self.assertEqual(response.status_code, 404)
        self.client.logout()

    def test_accept_quote(self):
        url = reverse("accept_quote", kwargs={'invoice_id':1})
        response = self.client.get(url)
        self.assertEqual(response.status_code, 302)
        self.client.login(username=self.username, ticket=self.ticket)
        response = self.client.get(url, follow=True)
        self.assertRedirects(response, reverse('list_invoices', kwargs={'customer_id':1}))
        self.assertContains(response, _("Invoices/Quotes for company"))
        self.client.logout()

    def test_accept_subscriptionquote(self):
        url = reverse("accept_subscriptionquote", kwargs={'subscription_id':1})
        response = self.client.get(url)
        self.assertEqual(response.status_code, 302)
        self.client.login(username=self.username, ticket=self.ticket)
        response = self.client.get(url, follow=True)
        self.assertRedirects(response, reverse('list_invoices', kwargs={'customer_id':1}))
        self.assertContains(response, _("Invoices/Quotes for company"))
        self.client.logout()

    def test_accept_quote404(self):
        url = reverse("accept_quote", kwargs={'invoice_id':123})
        response = self.client.get(url)
        self.client.login(username=self.username, ticket=self.ticket)
        response = self.client.get(url)
        self.assertEqual(response.status_code, 404)
        self.client.logout()

    def test_accept_subscriptionquote404(self):
        url = reverse("accept_subscriptionquote", kwargs={'subscription_id':123})
        response = self.client.get(url)
        self.client.login(username=self.username, ticket=self.ticket)
        response = self.client.get(url)
        self.assertEqual(response.status_code, 404)
        self.client.logout()

    def test_download_invoice404(self):
        url = reverse("download_invoice", kwargs={'invoice_id':123})
        response = self.client.get(url)
        self.client.login(username=self.username, ticket=self.ticket)
        response = self.client.get(url)
        self.assertEqual(response.status_code, 404)
        self.client.logout()

    def test_download_invoice(self):
        #FIXME: Make a subscription download test once the script is updated by Cyril
        url = reverse("download_invoice", kwargs={'invoice_id':1})
        response = self.client.get(url)
        self.assertEqual(response.status_code, 302)
        self.client.login(username=self.username, ticket=self.ticket)
        response = self.client.get(url, follow=True)
        self.assertEqual(response.status_code, 200)
        #self.assertContains(response, "http://testserver/invoice/download/invoice/1")
        self.client.logout()

    def test_hipay_invoice(self):
        url = reverse('hipay_invoice', kwargs={'invoice_id':1})
        response = self.client.get(url)
        self.assertEqual(response.status_code, 302)
        self.client.login(username=self.username, ticket=self.ticket)
        response = self.client.get(url)
        self.assertEqual(response['location'][:45], u'https://test-payment.hipay.com/index/mapi/id/')
        self.client.logout()

    def test_hipay_subscription(self):
        url = reverse('hipay_paysubs', kwargs={'subscription_id':1})
        response = self.client.get(url)
        self.assertEqual(response.status_code, 302)
        self.client.login(username=self.username, ticket=self.ticket)
        #FIXME:   File "/Users/wilane/src/isvtec/WebFinance/fo/../fo/hipay/hipay.py", line 322, in setURLOk
        # raise ValueError(_("""Invalid url %(url_ok)s""" %{'url_ok':URL_ok}))
        # ValueError: Invalid url http://testserver/invoice/hipay/payment/subscription/ok/1/1
        response = self.client.get(url)
        self.assertEqual(response['location'][:45], u'https://test-payment.hipay.com/index/mapi/id/')
        self.client.logout()

    def test_hipay_urls(self):
        keywords = {'ok':'successful', 'nook':'failed', 'cancel':'canceled'}
        tr = InvoiceTransaction(invoice_id=1)
        tr.save()
        for key, val in keywords.items():
            url = reverse("hipay_payment_url", kwargs={'action':key, 'invoice_id':1, 'internal_transid':tr.pk, 'payment_type':'invoice'})
            response = self.client.get(url)
            self.assertEqual(response.status_code, 200)
            self.assertTemplateUsed(response, 'invoice/hipay/%s_payment.html' %(key,))
            self.assertContains(response, _(val))
            self.client.logout()
            
    def test_parse_ack(self):
        ack = u"""<?xml version="1.0" encoding="UTF-8"?> <mapi>
<mapiversion>1.0</mapiversion> <md5content>c0783cc613bf025087b8bf5edecac824</md5content> <result>
<operation>capture</operation> <status>ok</status>
<date>2010-02-23</date>
<time>10:32:12 UTC+0000</time> <transid>4B83AEA905C49</transid> <origAmount>10.20</origAmount> <origCurrency>EUR</origCurrency> <idForMerchant>REF6522</idForMerchant> <emailClient>email_client@hipay.com</emailClient> <merchantDatas>
<_aKey_id_client>2000</_aKey_id_client>
<_aKey_credit>10</_aKey_credit> </merchantDatas>
<subscriptionId>753EA685B55651DC40F0C2784D5E1170</subscriptionId>
<refProduct0>REF6522</refProduct0>
</result> </mapi>"""
        tr = InvoiceTransaction(invoice_id=1)
        tr.save()
        url = reverse('hipay_ipn_ack', kwargs={'internal_transid':tr.pk,'payment_type':'invoice','invoice_id':1})
        self.client.login(username=self.username, ticket=self.ticket)
        response = self.client.post(url, {'xml': ack.decode('utf-8')})
        invoice = Invoices.objects.get(pk=1)
        self.assertEqual(response.status_code, 200)
        self.assertEqual(invoice.invoicetransaction_set.count(), 1)
        self.assertEqual(invoice.paid, False) # The 127.0.0.1 is not in the default allowed host

        # Change the settings to allow 127.0.0.1 to post ACK
        settings.HIPAY_ACK_SOURCE_IPS.append('127.0.0.1')
        response = self.client.post(url, {'xml': ack.decode('utf-8')})
        invoice = Invoices.objects.get(pk=1)
        self.assertEqual(response.status_code, 200)
        logger.info(invoice.invoicetransaction_set.count())
        self.assertEqual(invoice.invoicetransaction_set.count(), 2)
        self.assertEqual(invoice.paid, True) # The 127.0.0.1 should now be allowed

        tr = SubscriptionTransaction(subscription_id=1)
        tr.save()
        url = reverse('hipay_ipn_ack', kwargs={'internal_transid':tr.pk,'payment_type':'subscription','invoice_id':1})
        response = self.client.post(url, {'xml': ack.decode('utf-8')})
        subscription = Subscription.objects.get(pk=1)
        self.assertEqual(response.status_code, 200)
        self.assertEqual(SubscriptionTransaction.objects.count(), 2)
        self.assertEqual(subscription.subscriptiontransaction_set.count(), 2)


    def test_propagate_url_ack(self):
        ack = u"""<?xml version="1.0" encoding="UTF-8"?> <mapi>
<mapiversion>1.0</mapiversion> <md5content>c0783cc613bf025087b8bf5edecac824</md5content> <result>
<operation>capture</operation> <status>ok</status>
<date>2010-02-23</date>
<time>10:32:12 UTC+0000</time> <transid>4B83AEA905C49</transid> <origAmount>10.20</origAmount> <origCurrency>EUR</origCurrency> <idForMerchant>REF6522</idForMerchant> <emailClient>email_client@hipay.com</emailClient> <merchantDatas>
<_aKey_id_client>2000</_aKey_id_client>
<_aKey_credit>10</_aKey_credit> </merchantDatas>
<subscriptionId>753EA685B55651DC40F0C2784D5E1170</subscriptionId>
<refProduct0>REF6522</refProduct0>
</result> </mapi>"""
        url_ack = reverse('test_url_ack')
        # To expect this to work run the local django wsgi server (due to how
        # urllib2 resolve dns and the testserver), will mock this up
        tr = InvoiceTransaction(invoice_id=1, url_ack="http://127.0.0.1:8000%s"%url_ack)
        tr.save()
        url = reverse('hipay_ipn_ack', kwargs={'internal_transid':tr.pk,'payment_type':'invoice','invoice_id':1})
        self.client.login(username=self.username, ticket=self.ticket)
        # Change the settings to allow 127.0.0.1 to post ACK
        settings.HIPAY_ACK_SOURCE_IPS.append('127.0.0.1')
        response = self.client.post(url, {'xml': ack.decode('utf-8')})
        invoice = Invoices.objects.get(pk=1)
        self.assertEqual(response.status_code, 200)
        logger.info(invoice.invoicetransaction_set.count())
        self.assertEqual(invoice.invoicetransaction_set.count(), 2)
        self.assertEqual(invoice.paid, True) # The 127.0.0.1 should now be allowed

        tr = SubscriptionTransaction(subscription_id=1, url_ack="http://127.0.0.1:8000%s"%url_ack)
        tr.save()
        url = reverse('hipay_ipn_ack', kwargs={'internal_transid':tr.pk,'payment_type':'subscription','invoice_id':1})
        response = self.client.post(url, {'xml': ack.decode('utf-8')})
        subscription = Subscription.objects.get(pk=1)
        self.assertEqual(response.status_code, 200)
        self.assertEqual(SubscriptionTransaction.objects.count(), 2)
        self.assertEqual(subscription.subscriptiontransaction_set.count(), 2)

class ClientAPITestCase(TestCase):
    def setUp(self):
        user = User.objects.create_user(username='ousmane@wilane.org', email='ousmane@wilane.org', password=None)
        client = Clients.objects.get(pk=1)
        Clients2Users.objects.create(user=Users.objects.get(email='ousmane@wilane.org'), client=client)
        try:
            create_api_key(sender=User, instance=user, created=True)
        except:
            pass
        self.data = {'username':user.email, 'api_key':user.api_key.key}

    def test_gets(self):
        self.data.update({'format': 'json'})
        resp = self.client.get('/api/v1/', data=self.data)
        self.assertEqual(resp.status_code, 200)
        deserialized = json.loads(resp.content)
        self.assertEqual(len(deserialized), 7)
        self.assertEqual(deserialized['client'], {'list_endpoint': '/api/v1/client/', 'schema': '/api/v1/client/schema/'})

        resp = self.client.get('/api/v1/client/', data=self.data)
        self.assertEqual(resp.status_code, 200)
        deserialized = json.loads(resp.content)
        self.assertEqual(len(deserialized), 2)
        self.assertEqual(deserialized['meta']['limit'], 20)
        self.assertEqual(len(deserialized['objects']), 2)
        self.assertEqual([obj['name'] for obj in deserialized['objects']], [u'ISVTEC', u'ISVTEC2'])

        resp = self.client.get('/api/v1/client/1/', data=self.data)
        self.assertEqual(resp.status_code, 200)
        deserialized = json.loads(resp.content)
        self.assertEqual(len(deserialized), 14)
        self.assertEqual(deserialized['addr1'], u"1 rue Emile Zola")

        resp = self.client.get('/api/v1/client/set/2;1/', data=self.data)
        self.assertEqual(resp.status_code, 200)
        deserialized = json.loads(resp.content)
        self.assertEqual(len(deserialized), 1)
        self.assertEqual(len(deserialized['objects']), 2)
        self.assertEqual([obj['name'] for obj in deserialized['objects']], [u'ISVTEC2', u'ISVTEC'])

    def test_posts(self):
        post_data = '{"name":"From api 1", "city":"Dakar", "addr1":"Dakar", "country":"France", "zip":"100"}'
        resp = self.client.post('/api/v1/client/?%s' %urlencode(self.data), data=post_data, content_type='application/json')
        self.assertEqual(resp.status_code, 201)
        self.assertEqual(resp['location'], 'http://testserver/api/v1/client/4/')

        # make sure posted object exists
        resp = self.client.get('/api/v1/client/4/', data=self.data)
        self.assertEqual(resp.status_code, 200)
        obj = json.loads(resp.content)
        self.assertEqual(obj['name'], u'From api 1')
        self.assertEqual(obj['city'], u'Dakar')
        self.assertEqual(obj['country'], u'France')

    def test_puts(self):
        post_data = '{"addr1": "1 rue Emile Zola",  "zip": "69002", "name": "ISVTEC", "country": "France","city": "Dakar"}'

        resp = self.client.put('/api/v1/client/1/?%s' %urlencode(self.data), data=post_data, content_type='application/json')
        self.assertEqual(resp.status_code, 204)

        # make sure posted object exists
        resp = self.client.get('/api/v1/client/1/', data=self.data)
        self.assertEqual(resp.status_code, 200)
        obj = json.loads(resp.content)
        self.assertEqual(obj['city'], u'Dakar')


    def test_api_field_error(self):
        # FIXME: misleading test, the DB schema for the tests have no integrity
        # check ... legacy from the original bootstrap
        post_data = '{}'
        resp = self.client.post('/api/v1/client/?%s' %urlencode(self.data), data=post_data, content_type='application/json')

        # This should be 400 when the DB is fixed
        self.assertEqual(resp.status_code, 201)
        #self.assertEqual(resp.content, "Could not find ....")


    def test_options(self):
        resp = self.client.options('/api/v1/client/?%s' %urlencode(self.data))
        self.assertEqual(resp.status_code, 200)
        allows = 'GET,POST,PUT,DELETE,PATCH'
        self.assertEqual(resp['Allow'], allows)
        self.assertEqual(resp.content, allows)

        resp = self.client.options('/api/v1/client/1/?%s' %urlencode(self.data))
        self.assertEqual(resp.status_code, 200)
        allows = 'GET,POST,PUT,DELETE,PATCH'
        self.assertEqual(resp['Allow'], allows)
        self.assertEqual(resp.content, allows)

        resp = self.client.options('/api/v1/client/schema/?%s' %urlencode(self.data))
        self.assertEqual(resp.status_code, 200)
        allows = 'GET'
        self.assertEqual(resp['Allow'], allows)
        self.assertEqual(resp.content, allows)

        resp = self.client.options('/api/v1/client/set/2;1/?%s' %urlencode(self.data))
        self.assertEqual(resp.status_code, 200)
        allows = 'GET'
        self.assertEqual(resp['Allow'], allows)
        self.assertEqual(resp.content, allows)



class InvoiceAPITestCase(TestCase):
    def setUp(self):
        user = User.objects.create_user(username='ousmane@wilane.org', email='ousmane@wilane.org', password=None)
        client = Clients.objects.get(pk=1)
        Clients2Users.objects.create(user=Users.objects.get(email='ousmane@wilane.org'), client=client)
        try:
            create_api_key(sender=User, instance=user, created=True)
        except:
            pass
        self.data = {'username':user.email, 'api_key':user.api_key.key}

    def test_gets(self):
        self.data.update({'format': 'json'})
        resp = self.client.get('/api/v1/', data=self.data)
        self.assertEqual(resp.status_code, 200)
        deserialized = json.loads(resp.content)
        self.assertEqual(len(deserialized), 7)
        self.assertEqual(deserialized['invoice'], {'list_endpoint': '/api/v1/invoice/', 'schema': '/api/v1/invoice/schema/'})

        resp = self.client.get('/api/v1/invoice/', data=self.data)
        self.assertEqual(resp.status_code, 200)
        deserialized = json.loads(resp.content)
        self.assertEqual(len(deserialized), 2)
        self.assertEqual(deserialized['meta']['limit'], 20)
        self.assertEqual(len(deserialized['objects']), 1)
        self.assertEqual([obj['invoice_num'] for obj in deserialized['objects']], [u'201111100'])

        resp = self.client.get('/api/v1/invoice/1/', data=self.data)
        self.assertEqual(resp.status_code, 200)
        deserialized = json.loads(resp.content)
        self.assertEqual(len(deserialized), 28)
        self.assertEqual(deserialized['invoice_num'], u"201111100")


    def test_posts(self):
        post_data = '{"client":"/api/v1/client/1/", "invoice_date":"2011-11-10T00:00:00", "invoice_num":"141218", "invoicerows":[{"order": null,"description":"Premier article","df_price":17,"qty":3},{"order": null,"description":"Deuxième item","df_price":5,"qty":10}]}'
        resp = self.client.post('/api/v1/invoice/?%s' %urlencode(self.data), data=post_data, content_type='application/json')
        self.assertEqual(resp.status_code, 201)
        self.assertEqual(resp['location'], 'http://testserver/api/v1/invoice/2/')

        # make sure posted object exists
        resp = self.client.get('/api/v1/invoice/2/', data=self.data)
        self.assertEqual(resp.status_code, 200)
        obj = json.loads(resp.content)
        self.assertEqual(obj['invoice_num'], u'141218')

    def test_puts(self):
        post_data = '{"client":"/api/v1/invoice/1/", "invoice_date":"2011-11-10T00:00:00", "invoice_num":"141218"}'

        resp = self.client.put('/api/v1/invoice/1/?%s' %urlencode(self.data), data=post_data, content_type='application/json')
        self.assertEqual(resp.status_code, 204)

        # make sure posted object exists
        resp = self.client.get('/api/v1/invoice/1/', data=self.data)
        self.assertEqual(resp.status_code, 200)
        obj = json.loads(resp.content)
        self.assertEqual(obj['invoice_num'], u'141218')


    def test_api_field_error(self):
        # FIXME: misleading test, the DB schema for the tests have no integrity
        # check ... legacy from the original bootstrap
        post_data = '{"client":"/api/v1/invoice/1/", "date_facture":"2011-11-10T00:00:00"}'
        resp = self.client.post('/api/v1/invoice/?%s' %urlencode(self.data), data=post_data, content_type='application/json')

        # This should be 400 when the DB is fixed
        self.assertEqual(resp.status_code, 201)


    def test_options(self):
        resp = self.client.options('/api/v1/invoice/?%s' %urlencode(self.data))
        self.assertEqual(resp.status_code, 200)
        allows = 'GET,POST,PUT,DELETE,PATCH'
        self.assertEqual(resp['Allow'], allows)
        self.assertEqual(resp.content, allows)

        resp = self.client.options('/api/v1/invoice/1/?%s' %urlencode(self.data))
        self.assertEqual(resp.status_code, 200)
        allows = 'GET,POST,PUT,DELETE,PATCH'
        self.assertEqual(resp['Allow'], allows)
        self.assertEqual(resp.content, allows)

        resp = self.client.options('/api/v1/invoice/schema/?%s' %urlencode(self.data))
        self.assertEqual(resp.status_code, 200)
        allows = 'GET'
        self.assertEqual(resp['Allow'], allows)
        self.assertEqual(resp.content, allows)



class SubscriptionAPITestCase(TestCase):
    def setUp(self):
        user = User.objects.create_user(username='ousmane@wilane.org', email='ousmane@wilane.org', password=None)
        client = Clients.objects.get(pk=1)
        Clients2Users.objects.create(user=Users.objects.get(email='ousmane@wilane.org'), client=client)
        try:
            create_api_key(sender=User, instance=user, created=True)
        except:
            pass
        self.data = {'username':user.email, 'api_key':user.api_key.key}

    def test_posts(self):
        post_data = '{"client":"/api/v1/client/1/", "periodic_next_deadline":"2011-11-10T00:00:00", "ref_contrat":"141218", "subscriptionrowr":[{"description":"Premier article","prix_excl_vat":17,"qty":3},{"description":"Deuxième item","prix_excl_vat":5,"qty":10}]}'
        resp = self.client.post('/api/v1/subscription/?%s' %urlencode(self.data), data=post_data, content_type='application/json')
        self.assertEqual(resp.status_code, 201)
        self.assertEqual(resp['location'], 'http://testserver/api/v1/subscription/2/')

        # make sure posted object exists
        resp = self.client.get('/api/v1/subscription/1/', data=self.data)
        self.assertEqual(resp.status_code, 200)
        obj = json.loads(resp.content)
        self.assertEqual(obj['ref_contrat'], u'0412201101')

        
    def test_gets(self):
        #FIXME: Put subscription data in the fixtures
        self.test_posts()
        self.data.update({'format': 'json'})
        resp = self.client.get('/api/v1/', data=self.data)
        self.assertEqual(resp.status_code, 200)
        deserialized = json.loads(resp.content)
        self.assertEqual(len(deserialized), 7)
        self.assertEqual(deserialized['subscription'], {'list_endpoint': '/api/v1/subscription/', 'schema': '/api/v1/subscription/schema/'})

        resp = self.client.get('/api/v1/subscription/', data=self.data)
        self.assertEqual(resp.status_code, 200)
        deserialized = json.loads(resp.content)
        self.assertEqual(len(deserialized), 2)
        self.assertEqual(deserialized['meta']['limit'], 20)
        self.assertEqual(len(deserialized['objects']), 2)
        self.assertEqual([obj['ref_contrat'] for obj in deserialized['objects']], [u'0412201101',u'141218'])

        resp = self.client.get('/api/v1/subscription/1/', data=self.data)
        self.assertEqual(resp.status_code, 200)
        deserialized = json.loads(resp.content)
        self.assertEqual(len(deserialized), 11)
        self.assertEqual(deserialized['ref_contrat'], u"0412201101")


    def test_puts(self):
        self.test_posts()
        post_data = '{"client":"/api/v1/client/1/", "ref_contrat":"1412183"}'

        resp = self.client.put('/api/v1/subscription/1/?%s' %urlencode(self.data), data=post_data, content_type='application/json')
        self.assertEqual(resp.status_code, 204)

        # make sure posted object exists
        resp = self.client.get('/api/v1/subscription/1/', data=self.data)
        self.assertEqual(resp.status_code, 200)
        obj = json.loads(resp.content)
        self.assertEqual(obj['ref_contrat'], u'1412183')


    def test_api_field_error(self):
        # FIXME: misleading test, the DB schema for the tests have no integrity
        # check ... legacy from the original bootstrap
        post_data = '{"client":"/api/v1/client/1/", "ref_contrat":"2011111101"}'
        from django.db import IntegrityError
        try:
            resp = self.client.post('/api/v1/subscription/?%s' %urlencode(self.data), data=post_data, content_type='application/json')
        except IntegrityError:
            pass

        #self.assertEqual(resp.status_code, 500 or 400)


    def test_options(self):
        resp = self.client.options('/api/v1/subscription/?%s' %urlencode(self.data))
        self.assertEqual(resp.status_code, 200)
        allows = 'GET,POST,PUT,DELETE,PATCH'
        self.assertEqual(resp['Allow'], allows)
        self.assertEqual(resp.content, allows)

        resp = self.client.options('/api/v1/subscription/1/?%s' %urlencode(self.data))
        self.assertEqual(resp.status_code, 200)
        allows = 'GET,POST,PUT,DELETE,PATCH'
        self.assertEqual(resp['Allow'], allows)
        self.assertEqual(resp.content, allows)

        resp = self.client.options('/api/v1/subscription/schema/?%s' %urlencode(self.data))
        self.assertEqual(resp.status_code, 200)
        allows = 'GET'
        self.assertEqual(resp['Allow'], allows)
        self.assertEqual(resp.content, allows)


class HiPaySubscriptionAPITestCase(TestCase):
    def setUp(self):
        user = User.objects.create_user(username='ousmane@wilane.org', email='ousmane@wilane.org', password=None)
        client = Clients.objects.get(pk=1)
        Clients2Users.objects.create(user=Users.objects.get(email='ousmane@wilane.org'), client=client)
        try:
            create_api_key(sender=User, instance=user, created=True)
        except:
            pass
        self.data = {'username':user.email, 'api_key':user.api_key.key}

    def test_posts(self):
        post_data = '{"subscription":"/api/v1/subscription/1/"}'
        resp = self.client.post('/api/v1/paysubscription/?%s' %urlencode(self.data), data=post_data, content_type='application/json')
        self.assertEqual(resp.status_code, 201)
        self.assertEqual(resp['location'], 'http://testserver/api/v1/paysubscription/1/')

        # make sure posted object exists
        resp = self.client.get('/api/v1/paysubscription/1/', data=self.data)
        self.assertEqual(resp.status_code, 200)
        obj = json.loads(resp.content)
        self.assertEqual(obj['origCurrency'], u'EUR')


    def test_gets(self):
        #FIXME: Put subscription data in the fixtures
        self.test_posts()
        self.data.update({'format': 'json'})
        resp = self.client.get('/api/v1/', data=self.data)
        self.assertEqual(resp.status_code, 200)
        deserialized = json.loads(resp.content)
        self.assertEqual(len(deserialized), 7)
        self.assertEqual(deserialized['paysubscription'], {'list_endpoint': '/api/v1/paysubscription/', 'schema': '/api/v1/paysubscription/schema/'})

        resp = self.client.get('/api/v1/paysubscription/', data=self.data)
        self.assertEqual(resp.status_code, 200)
        deserialized = json.loads(resp.content)
        self.assertEqual(len(deserialized), 2)
        self.assertEqual(deserialized['meta']['limit'], 20)
        self.assertEqual(len(deserialized['objects']), 1)
        self.assertEqual([obj['origCurrency'] for obj in deserialized['objects']], [u'EUR'])

        resp = self.client.get('/api/v1/paysubscription/1/', data=self.data)
        self.assertEqual(resp.status_code, 200)
        deserialized = json.loads(resp.content)
        self.assertEqual(len(deserialized), 18)
        self.assertEqual(deserialized['origCurrency'], u"EUR")
        self.assertEqual(deserialized['redirect_url'][:45], u'https://test-payment.hipay.com/index/mapi/id/')


    def test_puts(self):
        # FIXME: Figure this out
        pass

    def test_api_field_error(self):
        # FIXME: misleading test, the DB schema for the tests have no integrity
        # check ... legacy from the original bootstrap
        post_data = '{"a":"null"}'

        #resp = self.client.post('/api/v1/paysubscription/?%s' %urlencode(self.data), data=post_data, content_type='application/json')

        #self.assertEqual(resp.status_code, 404)


    def test_options(self):
        resp = self.client.options('/api/v1/paysubscription/?%s' %urlencode(self.data))
        self.assertEqual(resp.status_code, 200)
        allows = 'GET,POST,DELETE'
        self.assertEqual(resp['Allow'], allows)
        self.assertEqual(resp.content, allows)

        resp = self.client.options('/api/v1/paysubscription/1/?%s' %urlencode(self.data))
        self.assertEqual(resp.status_code, 200)
        allows = 'GET,POST,DELETE'
        self.assertEqual(resp['Allow'], allows)
        self.assertEqual(resp.content, allows)

        resp = self.client.options('/api/v1/paysubscription/schema/?%s' %urlencode(self.data))
        self.assertEqual(resp.status_code, 200)
        allows = 'GET'
        self.assertEqual(resp['Allow'], allows)
        self.assertEqual(resp.content, allows)

class HiPayInvoiceAPITestCase(TestCase):
    def setUp(self):
        user = User.objects.create_user(username='ousmane@wilane.org', email='ousmane@wilane.org', password=None)
        client = Clients.objects.get(pk=1)
        Clients2Users.objects.create(user=Users.objects.get(email='ousmane@wilane.org'), client=client)
        try:
            create_api_key(sender=User, instance=user, created=True)
        except:
            pass
        self.data = {'username':user.email, 'api_key':user.api_key.key}

    def test_posts(self):
        post_data = '{"invoice":"/api/v1/invoice/1/"}'
        resp = self.client.post('/api/v1/payinvoice/?%s' %urlencode(self.data), data=post_data, content_type='application/json')
        self.assertEqual(resp.status_code, 201)
        self.assertEqual(resp['location'], 'http://testserver/api/v1/payinvoice/1/')

        # make sure posted object exists
        resp = self.client.get('/api/v1/payinvoice/1/', data=self.data)
        self.assertEqual(resp.status_code, 200)
        obj = json.loads(resp.content)
        self.assertEqual(obj['origCurrency'], u'EUR')


    def test_gets(self):
        #FIXME: Put subscription data in the fixtures
        self.test_posts()
        self.data.update({'format': 'json'})
        resp = self.client.get('/api/v1/', data=self.data)
        self.assertEqual(resp.status_code, 200)
        deserialized = json.loads(resp.content)
        self.assertEqual(len(deserialized), 7)
        self.assertEqual(deserialized['payinvoice'], {'list_endpoint': '/api/v1/payinvoice/', 'schema': '/api/v1/payinvoice/schema/'})

        resp = self.client.get('/api/v1/payinvoice/', data=self.data)
        self.assertEqual(resp.status_code, 200)
        deserialized = json.loads(resp.content)
        self.assertEqual(len(deserialized), 2)
        self.assertEqual(deserialized['meta']['limit'], 20)
        self.assertEqual(len(deserialized['objects']), 1)
        self.assertEqual([obj['origCurrency'] for obj in deserialized['objects']], [u'EUR'])

        resp = self.client.get('/api/v1/payinvoice/1/', data=self.data)
        self.assertEqual(resp.status_code, 200)
        deserialized = json.loads(resp.content)
        self.assertEqual(len(deserialized), 18)
        self.assertEqual(deserialized['origCurrency'], u"EUR")
        self.assertEqual(deserialized['redirect_url'][:45], u'https://test-payment.hipay.com/index/mapi/id/')


    def test_puts(self):
        # FIXME: Figure this out
        pass

    def test_api_field_error(self):
        # FIXME: misleading test, the DB schema for the tests have no integrity
        # check ... legacy from the original bootstrap
        post_data = '{"a":"null"}'

        #resp = self.client.post('/api/v1/payinvoice/?%s' %urlencode(self.data), data=post_data, content_type='application/json')

        #self.assertEqual(resp.status_code, 404)


    def test_options(self):
        resp = self.client.options('/api/v1/payinvoice/?%s' %urlencode(self.data))
        self.assertEqual(resp.status_code, 200)
        allows = 'POST,GET'
        self.assertEqual(resp['Allow'], allows)
        self.assertEqual(resp.content, allows)

        resp = self.client.options('/api/v1/payinvoice/1/?%s' %urlencode(self.data))
        self.assertEqual(resp.status_code, 200)
        allows = 'POST,GET'
        self.assertEqual(resp['Allow'], allows)
        self.assertEqual(resp.content, allows)

        resp = self.client.options('/api/v1/payinvoice/schema/?%s' %urlencode(self.data))
        self.assertEqual(resp.status_code, 200)
        allows = 'GET'
        self.assertEqual(resp['Allow'], allows)
        self.assertEqual(resp.content, allows)
