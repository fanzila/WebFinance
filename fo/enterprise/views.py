#!/usr/bin/env python
#-*- coding: utf-8 -*-
#Copyright (C) 2011 ISVTEC SARL
#$Id$
__author__ = "Ousmane Wilane ♟ <ousmane@wilane.org>"
__date__   = "Fri Nov 11 12:01:45 2011"


from django.contrib.auth.decorators import login_required
from django.shortcuts import render, redirect, get_object_or_404
from enterprise.form import EnterpriseForm
from enterprise.models import Users, Clients, Clients2Users
from django.utils.translation import ugettext_lazy as _

@login_required
def add_company(request):
    form = EnterpriseForm(request.POST or None)
    if form.is_valid():
        customer = form.save(commit=False)
        try:
            customer.id_user = Users.objects.get(email=request.user.email)
        except Users.DoesNotExist:
            user = Users.objects.create(email=request.user.email, login=request.user.email)
            customer.id_user = user
        customer.save()
        Clients2Users.objects.create(user=customer.id_user, client=customer)

        return redirect('home')
    return render(request, 'enterprise/add_company.html', {'form':form, 'title':_("Change company")})

@login_required
def change_company(request, customer_id):

    customer = get_object_or_404(Clients,id_client=customer_id)
    
    form = EnterpriseForm(request.POST or None, instance=customer)
    if form.is_valid():
        customer = form.save(commit=False)
        customer.id_user = Users.objects.get(email=request.user.email)
        customer.save()

        return redirect('home')
    return render(request, 'enterprise/add_company.html', {'form':form, 'title':_("Change company")})
