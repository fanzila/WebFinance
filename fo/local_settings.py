#!/usr/bin/env python
# -*- coding: utf-8 -*-
#Copyright (C) 2011 ISVTEC SARL
#$Id$

__author__ = "Ousmane Wilane ♟ <ousmane@wilane.org>"
__date__   = "Thu Nov 10 13:25:50 2011"


ADMINS = (
    ('Cyril Bouthors', 'cyril.bouthors@isvtec.com'),
    ('Ousmane Wilane', 'ousmane@wilane.org'),
)

MANAGERS = ADMINS

DATABASES = {
    'default': {
        'ENGINE': 'django.db.backends.mysql',
        'NAME': 'webfinance',
        'USER': 'root',
        'HOST': '10.42.0.1', 
        'PORT': '',
    }
}

CYBSSO_LOGIN = 'http://cybsso-dev.isvtec.com/'
LOGIN_URL = '/ssoaccounts/login'
