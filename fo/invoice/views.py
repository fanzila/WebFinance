#!/usr/bin/env python
#-*- coding: utf-8 -*-
#Copyright (C) 2011 ISVTEC SARL
#$Id$
__author__ = "Ousmane Wilane ♟ <ousmane@wilane.org>"
__date__   = "Fri Nov 11 09:43:42 2011"

from django.contrib.auth.decorators import login_required, permission_required
from fo.invoice.models import Invoices
from fo.enterprise.models import Clients, Users
from django.shortcuts import render
from django.template.context import RequestContext
from django.utils.translation import ugettext_lazy as _
from django.http import Http404

@login_required
def list_companies(request):
    # FIXME: We have to make the remote users linked to the User object
    try:
        current_user = Users.objects.get(id_user=2)
    except Users.DoesNotExist:
        raise Http404
    
    customer_list = current_user.customer.all()
    return render(request,'list_companies.html',
                              {'customer_list': customer_list})
    
@login_required
def list_invoices(request, customer_id):
    try:
        customer = Clients.objects.get(id_client=customer_id)
    except Clients.DoesNotExist:
        raise Http404
    
    return render(request, 'list_invoices.html',
                              {'invoice_list': customer.invoices_set.all(),
                               'company': customer})

@login_required
def detail_invoice(request, invoice_id):
    try:
        invoice = Invoices.objects.get(id_facture=invoice_id)
    except Invoices.DoesNotExist:
        raise Http404
    
    return render(request, 'detail_invoices.html',
                              {'invoice':invoice,
                               'invoice_details': invoice.invoicerows_set.order_by('ordre')})
