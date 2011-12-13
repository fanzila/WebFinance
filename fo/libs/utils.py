#!/usr/bin/env python
#-*- coding: utf-8 -*-
#Copyright (C) 2011 ISVTEC SARL
#$Id$
__author__ = "Ousmane Wilane ♟ <ousmane@wilane.org>"
__date__   = "Fri Dec  9 12:29:02 2011"

from os.path import join
from urlparse import urlparse
from django.template import loader
from django.template.base import TemplateDoesNotExist

def fo_get_template(base, name, parse=False):
    """ White label template search """
    url = urlparse(base)
    hostname = url.hostname
    if not url.hostname:
        hostname = base.split(':')[0]
    if parse:
        return loader.select_template([join(hostname, name), name])
    return [join(hostname, name), name]
    

def select_template(template_name_list):
    if not template_name_list:
        return None
    for template_name in template_name_list:
        try:
            loader.get_template(template_name)
            return template_name
        except TemplateDoesNotExist:
            continue
    # If we get here, none of the templates could be loaded
    return None
