#!/usr/bin/env python
#-*- coding: utf-8 -*-
#Copyright (C) 2011 ISVTEC SARL
#$Id$
from __future__ import with_statement

__author__ = "Ousmane Wilane ♟ <ousmane@wilane.org>"
__date__   = "Fri Nov 11 12:01:45 2011"


from datetime import datetime
from django.contrib.auth.decorators import login_required
from django.shortcuts import render, redirect, get_object_or_404, Http404
from enterprise.form import EnterpriseForm, InvitationForm
from enterprise.models import Users, Clients, Clients2Users, CompanyTypes, Invitation
from django.utils.translation import ugettext_lazy as _
from django.contrib import messages
from django.core.mail import send_mail
from django.conf import settings
from django.core.urlresolvers import reverse
import reversion


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
        with reversion.create_revision():
            customer.save()
            reversion.set_user(request.user)
            reversion.set_comment("Company added from %s" % request.META.get('REMOTE_ADDR', None))
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
        with reversion.create_revision():
            customer.save()
            reversion.set_user(request.user)
            reversion.set_comment("Company altered from %s" % request.META.get('REMOTE_ADDR', None))
        messages.add_message(request, messages.INFO, _('Company info saved'))
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
    
    form = InvitationForm(request.POST or None, qs=qs, initial={'company': qs.order_by('pk')[0] })
    if form.is_valid():
        # FIXME: Check if the user is not already in
        invitation = form.save(commit=False)
        invitation.guest=current_user
        invitation.save()
        base_host = "http%s://%s" %('s' if request.is_secure() else '',
                                    request.get_host())
        invitation.send_invitation(base_host)
        messages.add_message(request, messages.INFO, _("An email have been sent to the user, you'll be notified once the invitation is accepted"))
        return redirect('home')
    return render(request, 'enterprise/invite_user.html', {'form':form, 'title':_("Invite a user")})

@login_required
def accept_invitation(request, token):
    # FIXME: Send an email to let the owner know that the invitation have been
    # accepted
    try:
        current_user = Users.objects.get(email=request.user.email)
    except Users.DoesNotExist:
        current_user = Users.objects.create(email=request.user.email, login=request.user.email)

    invitation = get_object_or_404(Invitation, token=token, email=request.user.email, accepted=False)
    try:
        Clients2Users.objects.create(user=current_user, client=invitation.company)
    except:
        # FIXME: Except the user is already in
        # Fail silently for now
        return redirect('home')
    base_host = "http%s://%s" %('s' if request.is_secure() else '',
                                request.get_host())
    invitation.send_acceptation(base_host)
    invitation.accepted = True
    invitation.acceptation_date = datetime.now()
    invitation.save()
    messages.add_message(request, messages.INFO, _('Invitation Accepted'))
    return redirect('home')


@login_required
def revoke_invitation(request, token):
    try:
        current_user = Users.objects.get(email=request.user.email)
    except Users.DoesNotExist:
        current_user = Users.objects.create(email=request.user.email, login=request.user.email)
    invitation = get_object_or_404(Invitation, revocation_token=token, guest=current_user, accepted=True)
    try:
        c2u = Clients2Users.objects.get(user=Users.objects.get(email=invitation.email), client=invitation.company)
        c2u.delete()
    except:
        # FIXME: except not found
        pass
    #invitation.send_acceptation(base_host)
    #Don't delete it just mark it as revoked for future reference
    #invitation.delete()
    invitation.revoked = True
    invitation.revocation_date = datetime.now()
    invitation.save()
    messages.add_message(request, messages.INFO, _('Granted access revoked'))
    return redirect('home')

@login_required
def revoke_invitations(request):
    try:
        current_user = Users.objects.get(email=request.user.email)
    except Users.DoesNotExist:
        current_user = Users.objects.create(email=request.user.email, login=request.user.email)

    return render(request, 'enterprise/revoke_invitations.html', {'invitations':current_user.invitation_set.filter(accepted=True, revoked=False)})
