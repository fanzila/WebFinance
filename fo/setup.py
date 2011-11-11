#!/usr/bin/env python
# -*- coding: utf-8 -*-
#Copyright (C) 2011 ISVTEC SARL
# $Id$
__author__ = "Ousmane Wilane ♟ <ousmane@wilane.org>"
__date__   = "Thu Nov 10 19:28:44 2011"


from distutils.core import setup

setup(
    name='WebfinanceFo',
    version='0.1.0',
    author='ISVTEC SARL',
    author_email='info@isvtec.fr',
    packages = find_packages(),
    scripts=[],
    url='http://pypi.python.org/pypi/WebfinaceFo/',
    license='LICENSE.txt',
    description='Webfinance fronoffice.',
    long_description=open('README').read(),
    test_suite = "tests.runtests.runtests",
    include_package_data = True,
    install_requires=[
        "Django >= 1.3",
        "MySQL-python >= 1.2.3",
        "django-fixture-generator >= 0.2.0",
        "django-piston >= 0.2.3",
    ],
)
