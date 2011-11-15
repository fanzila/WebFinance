#!/usr/bin/env python
#-*- coding: utf-8 -*-
#Copyright (C) 2011 ISVTEC SARL
#$Id$
__author__ = "Ousmane Wilane ♟ <ousmane@wilane.org>"
__date__   = "Fri Nov 11 16:20:46 2011"


from django.forms import ModelForm
from enterprise.models import Clients

class EnterpriseForm(ModelForm):
    class Meta:
        model = Clients
        exclude = ['users', 'id_user', 'total_du_ht', 'has_devis', 'has_unpaid', 'id_client', 'password']
