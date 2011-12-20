#!/usr/bin/env python
# -*- coding: utf-8 -*-
#Copyright (C) 2011 ISVTEC SARL
#$Id$

__author__ = "Ousmane Wilane ♟ <ousmane@wilane.org>"
__date__   = "Thu Nov 10 13:21:00 2011"

import os
from logging.handlers import SysLogHandler

DEBUG = True
TEMPLATE_DEBUG = DEBUG
DIRNAME = os.path.dirname(__file__)

DATABASES = {
    'default': {
        'ENGINE': 'django.db.backends.sqlite3',
        'NAME': 'webfinancesqlite',
        'USER': '',
        'PASSWORD': '',
        'HOST': '', 
        'PORT': '',
    }
}

TEMPLATE_DIRS = (
    os.path.join(DIRNAME, '../', 'templates'),
)


# for django.contrib.auth default tests suite
AUTHENTICATION_BACKENDS =  ('libs.auth.WFRemoteUserBackend',
                            'django.contrib.auth.backends.ModelBackend',)

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
            'formatter': 'verbose'
        },
        'mail_admins': {
            'level': 'DEBUG',
            'class': 'django.utils.log.AdminEmailHandler',
        },
        'syslog': {
            'level':'CRITICAL',
            'class':'logging.handlers.SysLogHandler',
            'formatter': 'verbose',
            'facility': SysLogHandler.LOG_LOCAL2,
        },
    },
    'loggers': {
        'django': {
            'handlers':['null'],
            'propagate': True,
            'level':'DEBUG',
        },
        'django.request': {
            'handlers': ['null'],
            'level': 'ERROR',
            'propagate': False,
        },
        'wf': {
            'handlers': ['console', 'mail_admins', 'syslog'],
            'level': 'DEBUG',
        }
    }
}

TEST_ISVTEC_USER='test_user'
TEST_ISVTEC_PASSWORD='test_password'
