#!/usr/bin/env python
#-*- coding: utf-8 -*-
#Copyright (C) 2011 ISVTEC SARL
#$Id$
__author__ = "Ousmane Wilane ♟ <ousmane@wilane.org>"
__date__   = "Fri Nov 11 07:07:19 2011"


import os, sys
os.environ['DJANGO_SETTINGS_MODULE'] = 'tests.settings'

test_dir = os.path.dirname(__file__)
sys.path.insert(0, test_dir)

from django.test.utils import get_runner
from django.conf import settings

def runtests():
    test_runner = get_runner(settings)
    failures = test_runner([])
    sys.exit(failures)

if __name__ == '__main__':
    runtests()
