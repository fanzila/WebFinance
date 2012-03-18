#!/usr/bin/env python
# -*- coding: utf-8 -*-
#Copyright (C) 2011 ISVTEC SARL
#$Id$

__author__ = "Ousmane Wilane ♟ <ousmane@wilane.org>"
__date__   = "Thu Nov 10 13:25:50 2011"

from logging.handlers import SysLogHandler
import djcelery
from celery.schedules import crontab

DEBUG = True
ADMINS = (
#    ('Cyril Bouthors', 'cyril.bouthors@isvtec.com'),
    ('Ousmane Wilane', 'ousmane@wilane.org'),
)

MANAGERS = ADMINS

DATABASES = {
    'default': {
        'ENGINE': 'django.db.backends.mysql',
        'STORAGE_ENGINE': 'InnoDB',
        'NAME': 'webfinance',
        'USER': 'root',
        'PASSWORD': 'lptevtg',
        'HOST': '127.0.0.1',
#        'HOST': '10.42.0.1',
        'PORT': '',
    }
}

CYBSSO_LOGIN = 'http://cybsso-dev.isvtec.com/'
CYBSSO_LOGIN_URL = '/ssoaccounts/login'

EMAIL_HOST = '127.0.0.1'
DEFAULT_HOST = 'webfinance.wilane.org'
WEB_HOST = 'http://%s' % (DEFAULT_HOST,)
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
            'handlers':['null'],
            'propagate': True,
            'level':'DEBUG',
        },
        'django.request': {
            'handlers': ['console'],
            'level': 'ERROR',
            'propagate': True,
        },
        'social_auth': {
            'handlers': ['console'],
            'level': 'ERROR',
            'propagate': True,
        },
        'oauth_provider': {
            'handlers': ['console'],
            'level': 'ERROR',
            'propagate': True,
        },
        'isvtec': {
            'handlers': ['console', 'syslog'],
            'level': 'DEBUG',
            'propagate': True,
        },
        'wf': {
            'handlers': ['console', 'syslog'],
            'level': 'DEBUG',
            'propagate': True,
        }
    }
}

TASTYPIE_FULL_DEBUG=False

#HIPAY Parameters
HIPAY_GATEWAY="https://test-payment.hipay.com/order/"
HIPAY_ITEMACCOUNT="84971"
HIPAY_TAXACCOUNT="84971"
HIPAY_INSURANCEACCOUNT="84971"
HIPAY_FIXEDCOSTACCOUNT="84971"
HIPAY_SHIPPINGCOSTACCOUNT="84971"

HIPAY_CURRENCY="EUR"
HIPAY_EMAILACK="ousmane@wilane.org"
HIPAY_CAPTUREDAY="0" #HIPAY_MAPI_CAPTURE_IMMEDIATE"
HIPAY_BGCOLOR="#FFFFFF"
HIPAY_LOCALE="fr_FR"

HIPAY_LOGIN="84971"
HIPAY_PASSWORD="313666"
HIPAY_MEDIA="WEB"
HIPAY_RATING="ALL"
HIPAY_ID_FOR_MERCHANT="142545"
HIPAY_MERCHANT_SITE_ID="3194"
HIPAY_DEFAULT_CATEGORY="91"

HIPAY_DEFAULT_SUBSCRIPTION_FIRST_PAYMENT_DELAY='0H'
HIPAY_DEFAULT_SUBSCRIPTION_SUBS_PAYMENT_DELAY='1M'
HIPAY_DEFAULT_SUBSCRIPTION_CATEGORY="91"

HIPAY_ACK_SOURCE_IPS = ['195.158.241.241']

DEFAULT_TEMPLATE_DIR_PREFIX="default"
BASE_TEMPLATE = "base.html"
EMAIL_BASE_TEMPLATE='enterprise/emails/base.txt'
COMPANY_ADDRESS='enterprise/emails/address.txt'



SOCIAL_AUTH_IMPORT_BACKENDS = (
     'oauthclient',
)

SOCIAL_AUTH_ENABLED_BACKENDS = ('isvtec', 'twitter', 'google', 'google-oauth', 'github')


TEMPLATE_CONTEXT_PROCESSORS = ("django.contrib.auth.context_processors.auth",
                               "django.core.context_processors.debug",
                               "django.core.context_processors.i18n",
                               "django.core.context_processors.media",
                               "django.core.context_processors.static",
                               #"django.core.context_processors.tz",
                               "django.contrib.messages.context_processors.messages",
                               "fo.fo_context_processors.white_label",
                               "social_auth.context_processors.social_auth_by_name_backends",
                               )
ISVTEC_CONSUMER_KEY = 'dpf43f3p2l4k3l03'
ISVTEC_CONSUMER_SECRET = 'kd94hf93k423kf44'
ISVTEC_SERVER = 'oauth.wilane.org'
ISVTEC_LOGOUT_URL = "http://%s%s" %(ISVTEC_SERVER, '/accounts/logout')
TWITTER_CONSUMER_KEY              = 'KVNfuJv3hFdNDAFVyZ9Q'
TWITTER_CONSUMER_SECRET           = 'GAH3idtFFKilEmcnQZsEeEwm5xyRrohZ9KitW9qk54'

LOGIN_REDIRECT_URL = '/fo'
LOGIN_URL = '/fo/login/isvtec/'
LOGIN_ERROR_URL    = '/fo/login-error'

SOCIAL_AUTH_ERROR_KEY = 'social_errors'
SOCIAL_AUTH_EXPIRATION = 'expires'
SOCIAL_AUTH_SESSION_EXPIRATION = True
#SOCIAL_AUTH_USER_MODEL = 'fo.enterprise.Users'
SOCIAL_AUTH_COMPLETE_URL_NAME  = 'socialauth_complete'
SOCIAL_AUTH_ASSOCIATE_URL_NAME = 'socialauth_associate_complete'
SOCIAL_AUTH_ASSOCIATE_BY_MAIL = True
SESSION_SAVE_EVERY_REQUEST=True
SESSION_COOKIE_NAME='isvtecsession'

#SESSION_EXPIRE_AT_BROWSER_CLOSE = True
#SESSION_COOKIE_AGE = 60 * 10
#SESSION_SAVE_EVERY_REQUEST = True

djcelery.setup_loader()

from datetime import timedelta

# FIXME: Break this into components to make it more readable
BROKER_URL = "amqp://isvtec:n0p4ss3@localhost:5672/isvtec"
CELERY_RESULT_BACKEND = "amqp"

CELERY_IMPORTS = ("invoice.tasks", )
CELERY_RESULT_PERSISTENT = True
CELERYD_CONCURRENCY = 2

# From celery doc Disabling rate limits altogether is recommended if you don’t
# have any tasks using them. This is because the rate limit subsystem introduces
# quite a lot of complexity.
CELERY_DISABLE_RATE_LIMITS = True

CELERY_ENABLE_UTC = True
CELERY_SEND_TASK_ERROR_EMAILS = True
CELERYBEAT_SCHEDULE = {
    "runs-every-day": {
        "task": "invoice.tasks.subscription_reminder",
        "schedule": crontab(minute=0, hour=0),
        #"schedule":timedelta(seconds=30),
        "args": (None,),
    },
}

# Enable this on production
EMAIL_BACKEND = 'djcelery_email.backends.CeleryEmailBackend'
CELERY_SEND_EVENTS = True
CELERYD_HIJACK_ROOT_LOGGER = False
#CELERYBEAT_SCHEDULE_FILENAME = ?
