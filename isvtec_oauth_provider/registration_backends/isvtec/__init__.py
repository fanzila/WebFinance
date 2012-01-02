from django.conf import settings
from django.contrib.sites.models import RequestSite
from django.contrib.sites.models import Site
from urllib import unquote
from isvtec_profile.models import profile
from registration import signals
from registration.models import RegistrationProfile
from django.contrib.auth.models import User
from registration.backends.default import DefaultBackend
from django import forms
from django.utils.translation import ugettext_lazy as _
import logging
logger = logging.getLogger('wf')
attrs_dict = {'class': 'required'}

class ISVTECRegistrationForm(forms.Form):
    email = forms.EmailField(widget=forms.TextInput(attrs=dict(attrs_dict,
                                                               maxlength=75)),
                             label=_("E-mail"))
    first_name = forms.CharField(widget=forms.TextInput(attrs=attrs_dict),
                                label=_("First name"))
    last_name = forms.CharField(widget=forms.TextInput(attrs=attrs_dict),
                                label=_("Last name"))
    password1 = forms.CharField(widget=forms.PasswordInput(attrs=attrs_dict, render_value=False),
                                label=_("Password"))
    password2 = forms.CharField(widget=forms.PasswordInput(attrs=attrs_dict, render_value=False),
                                label=_("Password (again)"))
    tos = forms.BooleanField(widget=forms.CheckboxInput(attrs=attrs_dict),
                             label=_(u'I have read and agree to the Terms of Service'),
                             error_messages={'required': _("You must agree to the terms to register")})

    def clean_email(self):
        """
        Validate that the supplied email address is unique for the
        site.

        """

        if User.objects.filter(email__iexact=self.cleaned_data['email']):
            raise forms.ValidationError(_("This email address is already in use. Please supply a different email address."))
        return self.cleaned_data['email']

    def clean(self):
        """
        Verifiy that the values entered into the two password fields
        match. Note that an error here will end up in
        ``non_field_errors()`` because it doesn't apply to a single
        field.

        """
        if 'password1' in self.cleaned_data and 'password2' in self.cleaned_data:
            if self.cleaned_data['password1'] != self.cleaned_data['password2']:
                raise forms.ValidationError(_("The two password fields didn't match."))
        return self.cleaned_data

class ISVTECBackend(DefaultBackend):
    def register(self, request, **kwargs):
        email, password, first_name, last_name =  kwargs['email'], kwargs['password1'], kwargs['first_name'], kwargs['last_name']
        site = RequestSite(request)
        new_user = RegistrationProfile.objects.create_inactive_user(email, email,
                                                                    password, site)
        #save the oauth dance here
        p = profile(user=new_user, return_url=request.REQUEST.get('next', None))
        p.save()

        new_user.first_name = first_name
        new_user.last_name = last_name
        new_user.save()
        signals.user_registered.send(sender=self.__class__,
                                     user=new_user,
                                     request=request)
        return new_user

    def activate(self, request, activation_key):
        activated = RegistrationProfile.objects.activate_user(activation_key)
        if activated:
            signals.user_activated.send(sender=self.__class__,
                                        user=activated,
                                        request=request)
        return activated

    def registration_allowed(self, request):
        return getattr(settings, 'REGISTRATION_OPEN', True)

    def get_form_class(self, request):
        return ISVTECRegistrationForm

    def post_registration_redirect(self, request, user):
        return ('registration_complete', (), {})

    def post_activation_redirect(self, request, user):
        try:
            p = profile.objects.get(user=user)
        except profile.DoesNotExist:
            p = None

        if p and p.return_url:
            return (unquote(p.return_url), (), {})
        return ('registration_activation_complete', (), {})
