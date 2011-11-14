#!/usr/bin/env python
#-*- coding: utf-8 -*-
#Copyright (C) 2011 ISVTEC SARL
#$Id$
__author__ = "Ousmane Wilane ♟ <ousmane@wilane.org>"
__date__   = "Fri Nov 11 08:10:55 2011"

from django.contrib.auth.backends import ModelBackend
from django.contrib.auth.models import User
from libs.sso import *
from datetime import datetime

class WFRemoteUserBackend(ModelBackend):
    
    def authenticate(self, username=None, ticket=None):
        cybsso = CYBSSOService(CYBSSO_URL)
        tc = cybsso.TicketCheck(ticket, username)
        if isinstance(tc, datetime) and tc  > datetime.now():        
            try:
                # This will fail on admin: Duplicate key entry, we don't need
                # admin anyway 
                user = User.objects.get(email=username)
            except:
                user = User.objects.create_user(username,username, password=None)
            return user

        return None

        
