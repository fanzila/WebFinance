#!/usr/bin/env python
#-*- coding: utf-8 -*-
#Copyright (C) 2011 ISVTEC SARL
#$Id$
__author__ = "Ousmane Wilane ♟ <ousmane@wilane.org>"
__date__   = "Fri Nov 11 16:54:17 2011"

from django.test import TestCase
from django.core.urlresolvers import reverse
from django.utils.translation import ugettext_lazy as _
from invoice.models import Invoices, Transaction

class InvoiceTest(TestCase):
    def setUp(self):
        # We need a ticket and an account for test to pass before we use
        # selenium and friends
        self.username = 'ousmane@wilane.org'
        self.ticket = '2c314030278b9af4724352ba773ba2934bce6e59b12f776e01bdd0c2b47eeed10e551c53de697eda'

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
        

    def test_detail_invoice404(self):
        url = reverse("detail_invoice", kwargs={'invoice_id':123})
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

    def test_accept_quote404(self):
        url = reverse("accept_quote", kwargs={'invoice_id':123})
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
        url = reverse("download_invoice", kwargs={'invoice_id':1})
        response = self.client.get(url)
        self.assertEqual(response.status_code, 302)
        self.client.login(username=self.username, ticket=self.ticket)
        response = self.client.get(url, follow=True)
        self.assertEqual(response['Content-Type'], "application/pdf")
        self.client.logout()

    def test_hipay_invoice(self):
        url = reverse('hipay_invoice', kwargs={'invoice_id':1})
        response = self.client.get(url)
        self.assertEqual(response.status_code, 302)
        self.client.login(username=self.username, ticket=self.ticket)
        self.assertRaises(ValueError, self.client.get, url)
        self.client.logout()

    def test_hipay_urls(self):
        keywords = {'ok':'successful', 'nook':'failed', 'cancel':'canceled'}
        for key, val in keywords.items():
            url = reverse("hipay_payment_url", kwargs={'action':key, 'invoice_id':1})
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
        url = reverse('hipay_ipn_ack', kwargs={'invoice_id':1})
        self.client.login(username=self.username, ticket=self.ticket)
        response = self.client.post(url, {'xml': ack.decode('utf-8')})
        invoice = Invoices.objects.get(id_facture=1)
        self.assertEqual(response.status_code, 200)
        self.assertEqual(Transaction.objects.count(), 1)        
        self.assertEqual(invoice.is_paye, True)        


        
