#!/usr/bin/env python
#-*- coding: utf-8 -*-
#Copyright (C) 2011 ISVTEC SARL
#$Id$
__author__ = "Ousmane Wilane ♟ <ousmane@wilane.org>"
__date__   = "Fri Nov 11 07:01:12 2011"

from django.conf.urls.defaults import patterns, include, url
from django.views.generic.simple import redirect_to

from django.contrib import admin
admin.autodiscover()

urlpatterns = patterns('',
    url(r'^$',redirect_to, {'url': '/invoice/companies', 'permanent': False}, 'home'),
    url(r'^admin/', include(admin.site.urls)),
    url(r'^invoice/', include('fo.invoice.urls')),
    url(r'^enterprise/', include('fo.enterprise.urls')),
    url(r'^accounts/', include('django.contrib.auth.urls')),                       
)
