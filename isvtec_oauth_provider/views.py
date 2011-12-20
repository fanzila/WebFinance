#!/usr/bin/env python
#-*- coding: utf-8 -*-
#Copyright (C) 2011 ISVTEC SARL
#$Id$
__author__ = "Ousmane Wilane ♟ <ousmane@wilane.org>"
__date__   = "Sat Dec 17 07:51:42 2011"


from django.contrib.auth.decorators import login_required
from django.shortcuts import redirect, render
from django.utils.translation import ugettext as _
import oauth2 as oauth
from django.http import HttpResponse
from oauth_provider.models import Token
from forms import AuthorizeRequestTokenForm
from oauth_provider.store import store, InvalidTokenError
from oauth_provider.utils import  get_oauth_request, send_oauth_error
from django.http import HttpResponseBadRequest, HttpResponseRedirect
from urllib import urlencode
from oauth_provider.consts import OUT_OF_BAND
from django.core.urlresolvers import get_callable
from django.conf import settings
import logging
logger = logging.getLogger('wf')
try:
    import json
except ImportError:
    import simplejson as json


OAUTH_AUTHORIZE_VIEW = 'OAUTH_AUTHORIZE_VIEW'
OAUTH_CALLBACK_VIEW = 'OAUTH_CALLBACK_VIEW'

@login_required
def oauth_authorize(request, token, callback, params):
    """Normal authorization view, with the grant access form"""
    token = Token.objects.get(key=request.REQUEST.get('oauth_token'))
    form = AuthorizeRequestTokenForm(request.REQUEST)
    return render(request, 'authorize.html', {'form':form, 'token':token, 'user':request.user})

def verify_credentials(request):
    data = request.GET
    response_data = {}
    token = data.get('oauth_token', None)
    try:
        saved = Token.objects.get(key=token)
    except Token.ObjectDoesNotExist:
        logger.warn("Token not found %s" %token)
        return HttpResponse

    if saved.token_type is Token.ACCESS:
        response_data = dict(fullname=saved.user.username,
                             email=saved.user.email,
                             screen_name=saved.user.username,
                             last_name=saved.user.last_name,
                             first_name=saved.user.first_name,
                             name=saved.user.username,
                             id=saved.user.id)

    return HttpResponse(json.dumps(response_data), mimetype="application/json")

@login_required
def profile(request):
    return render(request, 'profile.html')

def registration_info(request, email):
    return render(request, 'registration_info.html')

@login_required
def exempt_user_authorization(request, token, callback, params):
    if 'oauth_token' not in request.REQUEST:
        return HttpResponseBadRequest('No request token specified.')

    oauth_request = get_oauth_request(request)

    try:
        request_token = store.get_request_token(request, oauth_request, request.REQUEST['oauth_token'])
    except InvalidTokenError:
        return HttpResponseBadRequest('Invalid request token.')

    if request.method in ('POST', 'GET'):
        if request.session.get('oauth', '') == request_token.key:
            request.session['oauth'] = ''

            request_token = store.authorize_request_token(request, oauth_request, request_token)
            args = { 'oauth_token': request_token.key }

            if request_token.callback is not None and request_token.callback != OUT_OF_BAND:
                response = HttpResponseRedirect('%s&%s' % (request_token.get_callback_url(), urlencode(args)))
            else:
                # try to get custom callback view
                callback_view_str = getattr(settings, OAUTH_CALLBACK_VIEW,
                                    'oauth_provider.views.fake_callback_view')
                try:
                    callback_view = get_callable(callback_view_str)
                except AttributeError:
                    raise Exception, "%s view doesn't exist." % callback_view_str
                response = callback_view(request, **args)
        else:
            response = send_oauth_error(oauth.Error(_('Action not allowed.')))
    else:
        # try to get custom authorize view
        authorize_view_str = getattr(settings, OAUTH_AUTHORIZE_VIEW,
                                    'oauth_provider.views.fake_authorize_view')
        try:
            authorize_view = get_callable(authorize_view_str)
        except AttributeError:
            raise Exception, "%s view doesn't exist." % authorize_view_str
        params = oauth_request.get_normalized_parameters()
        # set the oauth flag
        request.session['oauth'] = request_token.key
        response = authorize_view(request, request_token, request_token.get_callback_url(), params)
    return response
