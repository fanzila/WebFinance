#!/usr/bin/env python
#-*- coding: utf-8 -*-
#Copyright (C) 2011 ISVTEC SARL
#$Id$
__author__ = "Ousmane Wilane ♟ <ousmane@wilane.org>"
__date__   = "Fri Dec 16 07:59:45 2011"

import os
from logging.handlers import SysLogHandler
DEBUG = True
TEMPLATE_DEBUG = DEBUG
DIRNAME = os.path.dirname(__file__)
ADMINS = (
    ('Ousmane Wilane', 'ousmane@wilane.org'),
)

MANAGERS = ADMINS

DATABASES = {
    'default': {
        'ENGINE': 'django.db.backends.sqlite3', # Add 'postgresql_psycopg2', 'postgresql', 'mysql', 'sqlite3' or 'oracle'.
        'NAME': os.path.join(DIRNAME, 'isvtecsso.sqlite3'), # Or path to database file if using sqlite3.
        'USER': 'isvtecsso', # Not used with sqlite3.
        'PASSWORD': 'bq7%d!+3(^nj@9ij-iey8@m4a^=b3-ug6w%6mwj15ujbln*=@j', # Not used with sqlite3.
        'HOST': '127.0.0.1', # Set to empty string for localhost. Not used with sqlite3.
        'PORT': '', # Set to empty string for default. Not used with sqlite3.
    }
}

TIME_ZONE = 'America/Chicago'
LANGUAGE_CODE = 'fr-fr'
SITE_ID = 1
USE_I18N = True
USE_L10N = True
MEDIA_ROOT = ''
MEDIA_URL = ''
STATIC_ROOT = ''
STATIC_URL = '/static/'
ADMIN_MEDIA_PREFIX = '/static/admin/'
STATICFILES_DIRS = (
    os.path.join(DIRNAME, 'static'),
)
STATICFILES_FINDERS = (
    'django.contrib.staticfiles.finders.FileSystemFinder',
    'django.contrib.staticfiles.finders.AppDirectoriesFinder',
#    'django.contrib.staticfiles.finders.DefaultStorageFinder',
)
SECRET_KEY = 'bq7%d!+3(^nj@9ij-iey8@m4a^=b3-ug6w%6mwj15ujbln*=@j'
TEMPLATE_LOADERS = (
    'django.template.loaders.filesystem.Loader',
    'django.template.loaders.app_directories.Loader',
#     'django.template.loaders.eggs.Loader',
)

MIDDLEWARE_CLASSES = (
    'django.middleware.common.CommonMiddleware',
    'django.contrib.sessions.middleware.SessionMiddleware',
    'django.middleware.csrf.CsrfViewMiddleware',
    'django.contrib.auth.middleware.AuthenticationMiddleware',
    'django.contrib.messages.middleware.MessageMiddleware',
)

ROOT_URLCONF = 'isvtec_oauth_provider.urls'
TEMPLATE_DIRS = (
    os.path.join(DIRNAME, 'templates'),
)

INSTALLED_APPS = (
    'oauth_provider',
    'registration',
    'django.contrib.auth',
    'django.contrib.contenttypes',
    'django.contrib.sessions',
#    'django.contrib.sites',
    'django.contrib.messages',
    'django.contrib.staticfiles',
    'django.contrib.admin',
    'isvtec_profile',
)

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
            'formatter': 'verbose'
        },
        'mail_admins': {
            'level': 'DEBUG',
            'class': 'django.utils.log.AdminEmailHandler',
        },
        'syslog': {
            'level':'DEBUG',
            'class':'logging.handlers.SysLogHandler',
            'formatter': 'verbose',
            'facility': SysLogHandler.LOG_LOCAL2,
        },
    },
    'loggers': {
        'django': {
            #FIXME: Have to change this to syslog too, not sure
            'handlers': ['console', 'syslog'],
            #'handlers':['null'],
            'propagate': True,
            'level':'DEBUG',
        },
        'django.request': {
            'handlers': ['console', 'syslog'],
            'level': 'ERROR',
            'propagate': True,
        },
        'isvtec_oauth_provider': {
            'handlers': ['console', 'syslog'],
            'level': 'DEBUG',
            'propagate': True,
        },
        'views': {
            'handlers': ['console', 'syslog'],
            'level': 'DEBUG',
            'propagate': True,
        },
        'wf': {
            'handlers': ['console', 'syslog'],
            'level': 'DEBUG',
        }
    }
}

OAUTH_AUTHORIZE_VIEW = 'views.exempt_user_authorization'
#OAUTH_REALM_KEY_NAME = 'http://sso.isvtec.com'
#SOCIAL_AUTH_DEFAULT_USERNAME = 'username'
ACCOUNT_ACTIVATION_DAYS = 7
REGISTRATION_OPEN = True
