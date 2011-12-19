#!/usr/bin/env python
#-*- coding: utf-8 -*-
#Copyright (C) 2011 ISVTEC SARL
#$Id$
__author__ = "Ousmane Wilane ♟ <ousmane@wilane.org>"
__date__   = "Fri Nov 11 07:01:12 2011"

from django.conf.urls.defaults import patterns, include, url
from tastypie.api import Api
from fo.api.resources import InvoiceResource, ClientResource, InvoiceRowsResource, SubscriptionResource, SubscriptionRowResource, HiPayInvoice, HiPaySubscription
from django.contrib import admin
from django.conf import settings
admin.autodiscover()

v1_api = Api(api_name='v1')

v1_api.register(ClientResource())
v1_api.register(InvoiceResource())
v1_api.register(InvoiceRowsResource())

v1_api.register(SubscriptionResource())
v1_api.register(SubscriptionRowResource())

v1_api.register(HiPayInvoice())
v1_api.register(HiPaySubscription())

urlpatterns = patterns('',
    url(r'^$', 'fo.invoice.views.home', name='home'),
    url(r'^admin/', include(admin.site.urls)),
    url(r'^invoice/', include('fo.invoice.urls')),
    url(r'^enterprise/', include('fo.enterprise.urls')),
    url(r'^ssoaccounts/login', 'fo.views.ssologin', name='login_cybsso'),
    url(r'^ssoaccounts/logout', 'fo.views.ssologout', name='logout_cybsso'),
    url(r'^isvtecoauth/logout', 'fo.views.oauthlogout', name='logout_oauth'),
    url(r'^api/', include(v1_api.urls)),

    url(r'^login-error$', 'fo.views.login_error', name='login_error'),
    url(r'', include('social_auth.urls')),
)



if settings.DEBUG:
    urlpatterns += patterns('django.contrib.staticfiles.views',
        url(r'^static/(?P<path>.*)$', 'serve'),
    )
