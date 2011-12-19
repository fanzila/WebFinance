#!/usr/bin/env python
#-*- coding: utf-8 -*-
#Copyright (C) 2011 ISVTEC SARL
#$Id$
__author__ = "Ousmane Wilane ♟ <ousmane@wilane.org>"
__date__   = "Sat Dec 17 09:35:49 2011"


from django.shortcuts import render
from django.contrib.auth.decorators import login_required
from django.http import HttpResponse
@login_required
def home(request):
    return HttpResponse("Authenticated username: %s; email: %s; name: %s" %(request.user, request.user.email, request.user.get_full_name()))

def login_error(request):
    return render(request, 'error.html')


def login_form(request):
    return HttpResponse(request)
