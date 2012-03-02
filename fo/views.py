#!/usr/bin/env python
#-*- coding: utf-8 -*-
#Copyright (C) 2011 ISVTEC SARL
#$Id$
__author__ = "Ousmane Wilane ♟ <ousmane@wilane.org>"
__date__   = "Mon Nov 14 12:01:54 2011"


from django.shortcuts import redirect, render
from django.core.urlresolvers import reverse
from django.views.decorators.http import require_http_methods
from django.views.decorators.cache import never_cache
from django.conf import settings
from django.contrib.auth import login, authenticate
from django.contrib.auth import logout
from social_auth.views import auth, complete, associate, associate_complete, disconnect, auth_complete, SESSION_EXPIRATION, SOCIAL_AUTH_LAST_LOGIN, NEW_USER_REDIRECT, LOGIN_ERROR_URL, REDIRECT_FIELD_NAME, DEFAULT_REDIRECT
import logging

logger = logging.getLogger('isvtec')

@never_cache
@require_http_methods(["GET"])
def ssologin(request):
    if request.GET.get('next', False):
        request.session['next'] = request.GET.get('next')

    # We've just logged in and back from the SSO login page, save to session
    if request.GET.get('cybsso_ticket', False) and request.GET.get('cybsso_email', False):
        user = authenticate(username=request.GET['cybsso_email'],
                            ticket=request.GET['cybsso_ticket'])
        if user:
            request.session['cybsso_ticket'] = request.GET.get('cybsso_ticket')
            request.session['cybsso_email'] = request.GET.get('cybsso_email')
            login(request, user)
            return redirect(request.session.get('next'))

    if request.session.get('cybsso_ticket', False) and request.session.get('cybsso_email', False):
        user = authenticate(username=request.session.get('cybsso_email'),
                            ticket=request.session.get('cybsso_ticket'))
        if user:
            login(request, user)
            return redirect(request.session.get('next'))

    return redirect("%s?return_url=http%s://%s%s" %(settings.CYBSSO_LOGIN,
                                                       's' if request.is_secure() else '',
                                                       request.get_host(),
                                                       reverse('login_cybsso')))

@never_cache
@require_http_methods(["GET"])
def ssologout(request):
    logout(request)
    # FIXME: Create a HP that doesn't require auth or just leave the user at the
    # sso once logged out ?
    return redirect('%s?action=logout' %settings.CYBSSO_LOGIN)

@never_cache
def login_error(request):
    return render(request, 'error.html')


def oauthlogout(request):
    """Logs out user"""
    logout(request)
    return redirect(settings.ISVTEC_LOGOUT_URL)
