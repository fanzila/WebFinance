#!/usr/bin/env python
#-*- coding: utf-8 -*-
#Copyright (C) 2011 ISVTEC SARL
#$Id$
__author__ = "Ousmane Wilane ♟ <ousmane@wilane.org>"
__date__   = "Fri Nov 11 16:54:17 2011"

from django.test import TestCase
from django.core.urlresolvers import reverse
from django.utils.translation import ugettext_lazy as _

class InvoiceTest(TestCase):
    def setUp(self):
        # We need a ticket and an account for test to pass before we use
        # selenium and friends
        self.username = 'ousmane@wilane.org'
        self.ticket = '4972240c0cbffe0fe1a52f754a87accc9eab90bc4af43372a89e7c41d6e21d7ca7b5e912cf813130'

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
        print response.content
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



        
        
