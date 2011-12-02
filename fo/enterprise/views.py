#!/usr/bin/env python
#-*- coding: utf-8 -*-
#Copyright (C) 2011 ISVTEC SARL
#$Id$
__author__ = "Ousmane Wilane ♟ <ousmane@wilane.org>"
__date__   = "Fri Nov 11 12:01:45 2011"


from django.contrib.auth.decorators import login_required
from django.shortcuts import render, redirect, get_object_or_404, Http404
from enterprise.form import EnterpriseForm, InvitationForm
from enterprise.models import Users, Clients, Clients2Users, CompanyTypes, Invitation
from django.utils.translation import ugettext_lazy as _
from django.core.mail import send_mail
from django.template import loader, Context
from django.conf import settings
from django.core.urlresolvers import reverse


@login_required
def add_company(request):
    # FIXME: Make this view honor the returned_url if any for other apps that
    # will call it
    return_url = request.GET.get('return_url', None)

    form = EnterpriseForm(request.POST or None)
    if form.is_valid():
        customer = form.save(commit=False)
        try:
            customer.id_user = Users.objects.get(login=request.user.email)
        except Users.DoesNotExist:
            try:
                # FIXME: Remove these and do it just when the connection is first made
                user = Users.objects.create(email=request.user.email, login=request.user.email)
                customer.id_user = user
            except:
                # The login is not the email ('admin'), if this fail then crash.
                customer.id_user = Users.objects.get(email=request.user.email)            
        #Cyril wants this to be always 1
        customer.id_company_type = CompanyTypes.objects.get(pk=1)
        customer.save()
        Clients2Users.objects.create(user=customer.id_user, client=customer)

        if return_url:
            return redirect(return_url)
        return redirect('home')
    return render(request, 'enterprise/add_company.html', {'form':form, 'title':_("Add company")})

@login_required
def change_company(request, customer_id):
    # FIXME: Make this view honor the returned_url if any for other apps that
    # will call it
    customer = get_object_or_404(Clients,id_client=customer_id)
    return_url = request.GET.get('return_url', None)
    form = EnterpriseForm(request.POST or None, instance=customer)
    if form.is_valid():
        customer = form.save(commit=False)
        customer.id_user = Users.objects.get(email=request.user.email)
        customer.save()

        if return_url:
            return redirect(return_url)

        return redirect('home')
    return render(request, 'enterprise/add_company.html', {'form':form, 'title':_("Change company")})

@login_required
def invite_user(request):
    current_user = Users.objects.get(email=request.user.email)
    qs = current_user.clients_set.all()
    if not qs:
        raise Http404
    
    form = InvitationForm(request.POST or None, qs=qs)
    if form.is_valid():
        invitation = form.save()
        base_host = "http%s://%s" %('s' if request.is_secure() else '',
                                    request.get_host())
        subject = _("Invitation to join %(name)s from %(full_name)s" %{'name':invitation.company.nom, 'full_name':current_user.get_full_name()})
        message_template = loader.get_template('enterprise/emails/invitation.txt')
        message_context = Context({'recipient_name': invitation.get_full_name(),
                                   'sender_name': current_user.get_full_name(),
                                   'company': invitation.company.nom,
                                   'accept_url':"%s%s" %(base_host, reverse('accept_invitation',
                                                                            kwargs={'token':invitation.token}))
                                   })
        message = message_template.render(message_context)
        send_mail(subject, message, settings.DEFAULT_FROM_EMAIL, [invitation.email])
        return redirect('home')
    return render(request, 'enterprise/invite_user.html', {'form':form, 'title':_("Invite a user")})

@login_required
def accept_invitation(request, token):
    # FIXME: Send an email to let the owner know that the invitation have been
    # accepted
    try:
        current_user = Users.objects.get(login=request.user.email)
    except Users.DoesNotExist:
        current_user = Users.objects.create(email=request.user.email, login=request.user.email)
    invitation = get_object_or_404(Invitation, token=token, email=request.user.email)
    Clients2Users.objects.create(user=current_user, client=invitation.company)
    return redirect('home')
