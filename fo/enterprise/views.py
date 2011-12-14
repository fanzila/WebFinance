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
from enterprise.models import Users, Clients, Clients2Users, CompanyTypes, Invitation, Roles
from django.utils.translation import ugettext_lazy as _
from django.contrib import messages
from django.core.mail import send_mail
from django.conf import settings
from django.core.urlresolvers import reverse
import reversion
import logging
import operator
from fo.libs.utils import fo_get_template
logger = logging.getLogger('wf')

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
        customer.company_type = CompanyTypes.objects.get(pk=1)
        with reversion.create_revision():
            customer.save()
            reversion.set_user(request.user)
            reversion.set_comment("Company added from %s" % request.META.get('REMOTE_ADDR', None))
        manager = Roles.objects.get(name='manager')
        Clients2Users.objects.create(user=customer.id_user, client=customer, role=manager)

        if return_url:
            return redirect(return_url)
        return redirect('home')
    return render(request, fo_get_template(request.get_host(), 'enterprise/add_company.html'), {'form':form, 'title':_("Add company")})

@login_required
def change_company(request, customer_id):
    # FIXME: Make this view honor the returned_url if any for other apps that
    # will call it
    customer = get_object_or_404(Clients, pk=customer_id)
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
    return render(request, fo_get_template(request.get_host(),'enterprise/add_company.html'), {'form':form, 'title':_("Change company")})

@login_required
def resend_invitation(request, token):
    current_user = Users.objects.get(email=request.user.email)
    target_roles = Roles.objects.get(name='manager')
    managed_compagnies = Clients.objects.filter(clients2users__role=target_roles, clients2users__user=current_user)
    invitations = [c.invitation_set.all() for c in managed_compagnies]
    if invitations:
        qs = reduce(operator.or_, invitations)
    else:
        qs = Invitation.objects.none()
    invitation = get_object_or_404(qs,token=token)
    base_host = "http%s://%s" %('s' if request.is_secure() else '',
                                request.get_host())
    invitation.send_invitation(base_host)
    messages.add_message(request, messages.INFO, _("An invitation to %(email)s have been resent for %(company)s" %{'company':invitation.company, 'email':invitation.email}))
    return redirect('revoke_invitations')

@login_required
def invite_user(request):
    current_user = Users.objects.get(email=request.user.email)
    qs = current_user.clients_set.all()
    if not qs:
        raise Http404
    
    form = InvitationForm(request.POST or None, qs=qs, initial={'company': qs.order_by('pk')[0] })
    if form.is_valid():
        # Check if the user is not already in
        company = form.cleaned_data['company']
        email = form.cleaned_data['email']
        try:
            invited = Users.objects.get(email=email)
        except Users.DoesNotExist:
            invited=None

        try:
            Invitation.objects.get(email=email, company=company, guest=current_user, revoked=False)
            messages.add_message(request, messages.INFO, _("An invitation to %(email)s is already pending acceptation for %(company)s, resend the invitation if you think the previous one is lost" %{'company':company, 'email':email}))
            return redirect('revoke_invitations')
        except Invitation.DoesNotExist:
            pass

        try:
            Clients2Users.objects.get(user=invited, client=company)
        except Clients2Users.DoesNotExist:
            invitation = form.save(commit=False)
            invitation.guest=current_user
            invitation.save()
            base_host = "http%s://%s" %('s' if request.is_secure() else '',
                                        request.get_host())
            invitation.send_invitation(base_host)
            messages.add_message(request, messages.INFO, _("An email have been sent to the user, you'll be notified once the invitation is accepted"))
            return redirect('revoke_invitations')
        else:
            logger.warn(u"The user %s is already allowed to manage the company %s"%(invited.email, company))
            messages.add_message(request, messages.ERROR, _("The user %s is already allowed to manage the company %s" %(invited,company)))
    return render(request, fo_get_template(request.get_host(),'enterprise/invite_user.html'), {'form':form, 'title':_("Invite a user")})

@login_required
def accept_invitation(request, token):
    try:
        current_user = Users.objects.get(email=request.user.email)
    except Users.DoesNotExist:
        current_user = Users.objects.create(email=request.user.email, login=request.user.email)

    try:
        invitation = Invitation.objects.get(token=token, email=request.user.email, accepted=False)
    except Invitation.DoesNotExist:
        logger.warn(u"The user %s have no pending invitation (%s)"%(current_user, token))
        messages.add_message(request, messages.INFO, _("You don't have a pending invitation at this email address (%(email)s), check the email and make sure you log in with the email address  who've got the invitation email" %{'email':request.user.email}))
        return redirect('home')

    if invitation.revoked:
        logger.warn(u"The user %s have tried to accept a revoked pending invitation (%s)"%(current_user, token))
        messages.add_message(request, messages.INFO, _("This invitation have been revoked, contact the original sender for a new one"))
        return redirect('home')

    try:
        employee = Roles.objects.get(name='employee')
        Clients2Users.objects.create(user=current_user, client=invitation.company, role=employee)
    except:
        logger.warn(u"The user %s is already allowed to manage the company %s -- Invitation from %s is irrelevant"%(invitation.email, invitation.company, invitation.guest))
        messages.add_message(request, messages.INFO, _("You're already allowed to manage the company you're invited to"))
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
    target_roles = Roles.objects.get(name='manager')
    managed_compagnies = Clients.objects.filter(clients2users__role=target_roles, clients2users__user=current_user)
    invitations = [c.invitation_set.all() for c in managed_compagnies]
    invitations.append(current_user.invitation_set.all())
    if invitations:
        # All the companies where I'm the manager and the companies where I'm the guest
        qs = reduce(operator.or_, invitations)
    else:
        qs = Invitation.objects.none()
    invitation = get_object_or_404(qs, revocation_token=token)
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
    return redirect('revoke_invitations')

@login_required
def revoke_invitations(request):
    try:
        current_user = Users.objects.get(email=request.user.email)
    except Users.DoesNotExist:
        current_user = Users.objects.create(email=request.user.email, login=request.user.email)

    target_roles = Roles.objects.get(name='manager')
    # 1/ Search all the companies where the current user is a Manager
    # 2/ Show all the invitations concerning those companies (revocable)
    managed_compagnies = Clients.objects.filter(clients2users__role=target_roles, clients2users__user=current_user)
    invitations = [c.invitation_set.all() for c in managed_compagnies]
    invitations.append(current_user.invitation_set.all())
    if invitations:
        qs = reduce(operator.or_, invitations)
    else:
        qs = Invitation.objects.none()

    return render(request, fo_get_template(request.get_host(),'enterprise/revoke_invitations.html'), {'invitations':qs.filter(revoked=False)})
