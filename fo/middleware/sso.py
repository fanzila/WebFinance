#!/usr/bin/env python
#-*- coding: utf-8 -*-
#Copyright (C) 2011 ISVTEC SARL
#$Id$
__author__ = "Ousmane Wilane ♟ <ousmane@wilane.org>"
__date__   = "Tue Nov 15 08:57:51 2011"

from django.contrib.auth import authenticate, logout, login
from django.core.exceptions import ImproperlyConfigured
from django.utils.translation import ugettext_lazy as _

class CYBSSOMiddleware(object):
    """Check the ticket on CYBSSO on each request.
    """
    def process_request(self, request):
        if not hasattr(request, 'user'):
            raise ImproperlyConfigured(_("Malformed request")) 
        if not request.session.get('cybsso_ticket', False):
            return
        ticket = request.session.get('cybsso_ticket')
        email = request.session.get('cybsso_email')
        user = authenticate(username=email, ticket=ticket)
        if not user:
            logout(request)
        else:
            request.user = user
            login(request, user)
        
