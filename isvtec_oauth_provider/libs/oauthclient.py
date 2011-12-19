#!/usr/bin/env python
#-*- coding: utf-8 -*-
#Copyright (C) 2011 ISVTEC SARL
#$Id$
__author__ = "Ousmane Wilane ♟ <ousmane@wilane.org>"
__date__   = "Sat Dec 17 08:46:19 2011"


import logging
logger = logging.getLogger('wf')

from django.utils import simplejson
from oauth2 import Consumer as OAuthConsumer, Token, Request as OAuthRequest, \
                   SignatureMethod_HMAC_SHA1

from social_auth.backends import ConsumerBasedOAuth, OAuthBackend, USERNAME
from django.conf import settings
from django.contrib.auth import authenticate


# ISVTEC configuration
ISVTEC_SERVER = '127.0.0.1:8000'
ISVTEC_REQUEST_TOKEN_URL = 'http://%s/oauth/request_token' % ISVTEC_SERVER
ISVTEC_ACCESS_TOKEN_URL = 'http://%s/oauth/access_token' % ISVTEC_SERVER
# Note: oauth/authorize forces the user to authorize every time.
#       oauth/authenticate uses their previous selection, barring revocation.
ISVTEC_AUTHORIZATION_URL = 'http://%s/oauth/authenticate' % ISVTEC_SERVER
ISVTEC_CHECK_AUTH = 'http://127.0.0.1:8000/account/verify_credentials.json'


class ISVTECBackend(OAuthBackend):
    """ISVTEC OAuth authentication backend"""
    name = 'isvtec'
    EXTRA_DATA = [('id', 'id')]

    def get_user_details(self, response):
        """Return user details from ISVTEC account"""
        try:
            first_name, last_name = response['name'].split(' ', 1)
        except:
            first_name = response['name']
            last_name = ''
        return {USERNAME: response['screen_name'],
                'email': '',  # not supplied
                'fullname': response['name'],
                'first_name': first_name,
                'last_name': last_name}


class ISVTECAuth(ConsumerBasedOAuth):
    """ISVTEC OAuth authentication mechanism"""
    AUTHORIZATION_URL = ISVTEC_AUTHORIZATION_URL
    REQUEST_TOKEN_URL = ISVTEC_REQUEST_TOKEN_URL
    ACCESS_TOKEN_URL = ISVTEC_ACCESS_TOKEN_URL
    SERVER_URL = ISVTEC_SERVER
    AUTH_BACKEND = ISVTECBackend
    SETTINGS_KEY_NAME = 'ISVTEC_CONSUMER_KEY'
    SETTINGS_SECRET_NAME = 'ISVTEC_CONSUMER_SECRET'

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
