#!/usr/bin/env python
# -*- coding: utf-8 -*-
#Copyright (C) 2011 ISVTEC SARL
#$Id$

__author__ = "Ousmane Wilane ♟ <ousmane@wilane.org>"
__date__   = "Thu Nov 10 13:25:50 2011"

DEBUG = True
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

HIPAY_GATEWAY="https://test-payment.hipay.com/order/"
EMAIL_HOST = '10.42.0.1'
DEFAULT_FROM_EMAIL = 'no_reply@isvtec.com'


LOGGING = {
    'version': 1,
    'disable_existing_loggers': True,
    'formatters': {
        'verbose': {
            'format': '%(levelname)s %(asctime)s %(module)s %(process)d %(thread)d %(message)s'
        },
        'simple': {
            'format': '%(levelname)s %(message)s'
        },
    },
    'handlers': {
        'null': {
            'level':'DEBUG',
            'class':'django.utils.log.NullHandler',
        },
        'console':{
            'level':'DEBUG',
            'class':'logging.StreamHandler',
            'formatter': 'simple'
        },
        'mail_admins': {
            'level': 'DEBUG',
            'class': 'django.utils.log.AdminEmailHandler',
        }
    },
    'loggers': {
        'django': {
            'handlers':['null', 'console'],
            'propagate': True,
            'level':'DEBUG',
        },
        'django.request': {
            'handlers': ['mail_admins'],
            'level': 'ERROR',
            'propagate': False,
        },
        'fo.custom': {
            'handlers': ['console', 'mail_admins'],
            'level': 'INFO',
        }
    }
}

TASTYPIE_FULL_DEBUG=True
