#!/usr/bin/env python
#-*- coding: utf-8 -*-
#Copyright (C) 2011 ISVTEC SARL
#$Id$
__author__ = "Ousmane Wilane ♟ <ousmane@wilane.org>"
__date__   = "Mon Dec 12 07:28:22 2011"

from django.conf import settings

import os
def white_label(request):
    kwargs = {}
    white_label = request.get_host().split(':')[0] # GEt rid of the port if any
    if os.path.isfile(os.path.join(white_label, settings.BASE_TEMPLATE)):
        kwargs['BASE_TEMPLATE'] = os.path.join(white_label, settings.BASE_TEMPLATE)
    else:
        kwargs['BASE_TEMPLATE'] = settings.BASE_TEMPLATE
    # Please put your statics in just one place and set the variable
    # STATIC_FILE_DIRS in settings
    for s in settings.STATICFILES_DIRS:
        if os.path.isdir(os.path.join(s, white_label)):
            kwargs['base'] = os.path.join(settings.STATIC_URL, white_label)
    else:
        # defaullt at the root of static file
        kwargs['base'] =  settings.STATIC_URL
    return kwargs
