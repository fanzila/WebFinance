#!/usr/bin/env python
#-*- coding: utf-8 -*-
#Copyright (C) 2011 ISVTEC SARL
#$Id$
__author__ = "Ousmane Wilane ♟ <ousmane@wilane.org>"
__date__   = "Fri Nov 11 12:01:45 2011"


from django.contrib.auth.decorators import login_required, permission_required
from fo.invoice.models import Invoices
from fo.enterprise.models import Clients, Users, Clients2Users
from django.shortcuts import render, redirect
from django.template.context import RequestContext
from django.utils.translation import ugettext_lazy as _
from enterprise.form import EnterpriseForm
@login_required
def add_company(request):
    form = EnterpriseForm(request.POST or None)
    if form.is_valid():
        customer = form.save(commit=False)
        for u in customer.users.all():
            Clients2Users.objects.create(client=customer,user=u)
        customer.save()
        #customer.save_m2m()
            
        # FIXME: Let's add the user to the company he've just created
        #customer.users_set.create(CURRENT_USER)
        return redirect('home')
    return render(request, 'add_company.html', {'form':form})
