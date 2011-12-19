#!/usr/bin/env python
#-*- coding: utf-8 -*-
#Copyright (C) 2011 ISVTEC SARL
#$Id$
__author__ = "Ousmane Wilane ♟ <ousmane@wilane.org>"
__date__   = "Sat Dec 17 07:51:42 2011"


from django.contrib.auth.decorators import login_required
from django.shortcuts import redirect, render
from django.http import HttpResponse, HttpResponseBadRequest, HttpResponseRedirect
from oauth_provider.models import Token
import logging
logger = logging.getLogger('wf')
import json

@login_required
def oauth_authorize(request, token, callback, params):
    return render(request, 'authorize.html',{'params': request.REQUEST})

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
                                         name=saved.user.username,
                                         id=saved.user.id)

    return HttpResponse(json.dumps(response_data), mimetype="application/json")

        
