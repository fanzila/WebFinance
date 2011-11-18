#!/usr/bin/env python
#-*- coding: utf-8 -*-
#Copyright (C) 2011 ISVTEC SARL
#$Id$
__author__ = "Ousmane Wilane ♟ <ousmane@wilane.org>"
__date__   = "Fri Nov 11 09:43:42 2011"

from django.contrib.auth.decorators import login_required
from fo.invoice.models import Invoices
from fo.enterprise.models import Clients, Users
from django.shortcuts import render, redirect, get_object_or_404, Http404
from subprocess import call
from django.conf import settings
from django.http import HttpResponse
from os.path import join
import operator
from django.db.models import Q

@login_required
def list_companies(request):
    try:
        current_user = Users.objects.get(email=request.user.email)
        # We keep a loose coupling with native Users database from the backend
        customer_list = current_user.clients_set.all() | current_user.creator.all()
        
    except Users.DoesNotExist:
        customer_list = None

    return render(request,'invoice/list_companies.html',
                              {'customer_list': customer_list})
    
@login_required
def list_invoices(request, customer_id):
    current_user = Users.objects.get(email=request.user.email)
    customer = get_object_or_404(current_user.clients_set, id_client=customer_id)    
    return render(request, 'invoice/list_invoices.html',
                              {'invoice_list': customer.invoices_set.filter(type_doc='facture', is_paye=False),
                               'quote_list': customer.invoices_set.filter(type_doc='devis'),
                               'company': customer})

@login_required
def detail_invoice(request, invoice_id):
    current_user = Users.objects.get(email=request.user.email)
    qs  = reduce(operator.or_,[c.invoices_set.all() for c in current_user.clients_set.all()])
    invoice = get_object_or_404(qs, id_facture=invoice_id)    
    
    return render(request, 'invoice/detail_invoices.html',
                              {'invoice':invoice,
                               'invoice_details': invoice.invoicerows_set.order_by('ordre')})

@login_required
def accept_quote(request, invoice_id):
    current_user = Users.objects.get(email=request.user.email)
    qs  = reduce(operator.or_,[c.invoices_set.all() for c in current_user.clients_set.all()])
    quote = get_object_or_404(qs, id_facture=invoice_id)    
    quote.type_doc = 'facture'
    quote.save()
    return redirect('list_invoices', customer_id=quote.client.id_client)

@login_required
def hipay_invoice(request, invoice_id):
    current_user = Users.objects.get(email=request.user.email)
    qs  = reduce(operator.or_,[c.invoices_set.all() for c in current_user.clients_set.all()])    
    quote = get_object_or_404(qs, id_facture=invoice_id)    
    return redirect('list_invoice', customer_id=quote.client.id_client)


@login_required
def download_invoice(request, invoice_id):
    #FIXME: Make the the called script accept a configurable directory, Cyril
    #can you fix that please.
    current_user = Users.objects.get(email=request.user.email)
    qs  = reduce(operator.or_,[c.invoices_set.all() for c in current_user.clients_set.all()])    
    invoice = get_object_or_404(qs, id_facture=invoice_id)
    if not call([settings.INVOICE_PDF_GENERATOR, str(invoice.id_facture)]):
        filename = 'Facture_%s_%s.pdf' %(invoice.num_facture, invoice.client.nom)
        filepath = join(settings.INVOICE_PDF_DIR, filename)        
        response = HttpResponse(mimetype='application/pdf')
        response['Content-Disposition'] = 'attachment; filename=%s' %(filename,)
        response.write(open(filepath).read())
        return response

    raise Http404
