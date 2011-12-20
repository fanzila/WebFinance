#!/usr/bin/env python
#-*- coding: utf-8 -*-
#Copyright (C) 2011 ISVTEC SARL
#$Id$
__author__ = "Ousmane Wilane ♟ <ousmane@wilane.org>"
__date__   = "Sat Dec 17 08:46:19 2011"


import logging
logger = logging.getLogger('wf')

from django.utils import simplejson
from social_auth.backends import ConsumerBasedOAuth, OAuthBackend, USERNAME
from django.conf import settings
from django.contrib.auth.models import User
from django.db import models
from tastypie.models import create_api_key

# ISVTEC configuration
ISVTEC_REQUEST_TOKEN_URL = 'http://%s/oauth/request_token/' % settings.ISVTEC_SERVER
ISVTEC_ACCESS_TOKEN_URL = 'http://%s/oauth/access_token/' % settings.ISVTEC_SERVER
ISVTEC_AUTHORIZATION_URL = 'http://%s/oauth/authorize/' % settings.ISVTEC_SERVER
ISVTEC_CHECK_AUTH = 'http://%s/accounts/verify_credentials.json' % settings.ISVTEC_SERVER

class ISVTECBackend(OAuthBackend):
    """ISVTEC OAuth authentication backend"""
    name = 'isvtec'
    EXTRA_DATA = [('id', 'id'), ('first_name', 'first_name'), ('last_name', 'last_name')]

    def get_user_details(self, response):
        """Return user details from ISVTEC account"""
        return {USERNAME: response['email'],
                'email': response['email'],
                'fullname': response['name'],
                'first_name': response['first_name'],
                'last_name': response['last_name']}


class ISVTECAuth(ConsumerBasedOAuth):
    """ISVTEC OAuth authentication mechanism"""
    AUTHORIZATION_URL = ISVTEC_AUTHORIZATION_URL
    REQUEST_TOKEN_URL = ISVTEC_REQUEST_TOKEN_URL
    ACCESS_TOKEN_URL = ISVTEC_ACCESS_TOKEN_URL
    SERVER_URL = settings.ISVTEC_SERVER
    AUTH_BACKEND = ISVTECBackend
    SETTINGS_KEY_NAME = 'ISVTEC_CONSUMER_KEY'
    SETTINGS_SECRET_NAME = 'ISVTEC_CONSUMER_SECRET'
    #name = 'isvtec'

    def user_data(self, access_token):
        """Return user data provided"""
        request = self.oauth_request(access_token, ISVTEC_CHECK_AUTH)
        json = self.fetch_response(request)
        try:
            return simplejson.loads(json)
        except ValueError:
            return None


# Backend definition
BACKENDS = {
    'isvtec': ISVTECAuth,
}

models.signals.post_save.connect(create_api_key, sender=User)
