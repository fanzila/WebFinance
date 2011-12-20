#!/usr/bin/env python
#-*- coding: utf-8 -*-
#Copyright (C) 2011 ISVTEC SARL
#$Id$
__author__ = "Ousmane Wilane ♟ <ousmane@wilane.org>"
__date__   = "Fri Nov 11 08:10:55 2011"

from django.contrib.auth.backends import ModelBackend
from django.contrib.auth.models import User
from datetime import datetime
from libs.sso import CYBSSOService, CYBSSO_URL
from fo.enterprise.models import Users

class WFRemoteUserBackend(ModelBackend):
    def authenticate(self, username=None, ticket=None):
        cybsso = CYBSSOService(CYBSSO_URL)
        tc = cybsso.TicketCheck(ticket, username)
        if isinstance(tc, datetime) and tc  > datetime.now():
            userinfo = cybsso.UserGetInfo(username)
            try:
                # This will fail on admin: Duplicate key entry, we don't need
                # admin anyway 
                user = User.objects.get(email=username)
            except User.DoesNotExist:
                user = User.objects.create_user(username,username, password=None)

            try:
                current_user = Users.objects.get(email=username)
            except Users.DoesNotExist:
                current_user = Users.objects.create(email=username, login=username)

            if userinfo:
                current_user.first_name = userinfo.get('firstname', None)
                current_user.last_name = userinfo.get('lastname', None)
                current_user.save()
            return user

        return None

class WFMockRemoteUserBackend(ModelBackend):
    def authenticate(self, username=None, ticket=None):
        try:
            user = User.objects.get(email=username)
        except User.DoesNotExist:
            user = User.objects.create_user(username,username, password=None)

        try:
            Users.objects.get(email=username)
        except Users.DoesNotExist:
            Users.objects.create(email=username, login=username)
        return user
