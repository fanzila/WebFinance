#!/usr/bin/env python
#-*- coding: utf-8 -*-
#Copyright (C) 2011 ISVTEC SARL
#$Id$
__author__ = "Ousmane Wilane ♟ <ousmane@wilane.org>"
__date__   = "Fri Nov 11 16:54:17 2011"

from django.test import TestCase
from django.core.urlresolvers import reverse
from enterprise.models import Clients
from django.utils.translation import ugettext_lazy as _
from django.contrib.auth.models import User

class AddCompanyTest(TestCase):
    def setUp(self):
         u = User.objects.create(username='admin')
         u.set_password('admin')
         u.save()
         

    def test_add_company(self):
        url = reverse("add_company")
        response = self.client.get(url)

        self.assertEqual(response.status_code, 302)

        self.client.login(username='admin', password='admin')
        response = self.client.get(url)
        self.assertEqual(response.status_code, 200)

        self.assertTemplateUsed(response, 'add_company.html')

        count = Clients.objects.count()
        response = self.client.post(url,
                                    {'id_client': 142,
                                     'has_devis': 0,
                                     'id_user': 1,
                                     'nom': 'foo baz'},
                                    follow = True)
        self.assertEqual(Clients.objects.count(), count + 1)
        self.assertContains(response, _("My companies"))
        self.client.logout()
        
