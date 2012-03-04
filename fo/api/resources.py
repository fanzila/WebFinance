#!/usr/bin/env python
#-*- coding: utf-8 -*-
#Copyright (C) 2011 ISVTEC SARL
#$Id$
__author__ = "Ousmane Wilane ♟ <ousmane@wilane.org>"
__date__   = "Sat Nov 26 12:12:29 2011"


from tastypie.resources import ModelResource, Resource

from invoice.models import Invoices, Clients, InvoiceRows, Subscription, SubscriptionRow, SubscriptionTransaction, InvoiceTransaction
from enterprise.models import Users, Clients2Users, CompanyTypes
from libs.hipay import simplepayment, subscriptionpayment
from tastypie import fields
from tastypie.authentication import ApiKeyAuthentication
from tastypie.authorization import Authorization
from django.core.exceptions import ObjectDoesNotExist, MultipleObjectsReturned
from django.conf.urls.defaults import url
from django.conf import settings
from tastypie.http import *
import operator
import logging
import urllib2

USER_AGENT = 'WebFinace-FO API/1.0'

try:
    import json
except ImportError:
    import simplejson as json

logger = logging.getLogger('wf')

class HeaderApiKeyAutentication(ApiKeyAuthentication):
    def is_authenticated(self, request, **kwargs):
        """
        Finds the user and checks their API key.

        Should return either ``True`` if allowed, ``False`` if not or an
        ``HttpResponse`` if you need something custom.
        """
        from django.contrib.auth.models import User
        logger.warn("REQUEST META=%s GET=%s POST%s" %(request.META, request.GET, request.POST))

        username = request.GET.get('username') or request.POST.get('username') or request.META.get('HTTP_USERNAME')
        api_key = request.GET.get('api_key') or request.POST.get('api_key') or request.META.get('HTTP_API_KEY') or request.META.get('HTTP_APIKEY')

        if not username or not api_key:
            logger.warn("[401 UNAUTHORIZED] Either username or api_key not provided in the headers or in posted/geted vars, check with your client")
            return self._unauthorized()

        try:
            user = User.objects.get(email=username)
        except (User.DoesNotExist, User.MultipleObjectsReturned), e:
            logger.error("[401 UNAUTHORIZED] For some reasons the user haven't been created when OAuth completed the auth process, this is likely a bug: ```%s'''" %e)
            return self._unauthorized()

        request.user = user
        return self.get_key(user, api_key, request)

    def get_content(self, url, data=None):
        """Return content for given url, if data is not None, then a POST
        request will be issued, otherwise GET will be used"""
        data = data and urllib.urlencode(data, doseq=True) or data
        request = urllib2.Request(url)
        agent = urllib2.build_opener()
        request.add_header('User-Agent', USER_AGENT)
        return ''.join(agent.open(request, data=data).readlines())


    def get_key(self, user, api_key, request=None):
        """
        Attempts to find the API key for the user from the OAuth Provider.
        """
        try:
            u = user.social_auth.all()[0]
        except IndexError, e:
            #FIXME: This is very dangerous fix it
            if request.META.get('SERVER_NAME', None) == 'testserver':
                return True
            return False
        logger.warn("The user extra data is %s"%u.extra_data)
        try:
            # FIXME: Use requests instead
            response = self.get_content("http://%s/accounts/verify_credentials.json?%s"%(settings.ISVTEC_SERVER,
                                                                                         u.extra_data.get('access_token')))
        except Exception, e:
            logger.warn("There was an error trying to pull the user's apikey from the oauth server %s"%e)
        result = json.loads(response)
        return result.get('apikey', None) == api_key

class ClientResource(ModelResource):
    def apply_authorization_limits(self, request, object_list):
        if not request:
            return Clients.objects.distinct()
        current_user = Users.objects.get(email=request.user.username)
        queryset = current_user.clients_set.all()
        #FIXME:Shouldn't need this
        return queryset.distinct()

    def get_object_list(self, request):
        if not request:
            return Clients.objects.distinct()
        current_user = Users.objects.get(email=request.user.username)
        queryset = current_user.clients_set.all()
        return operator.and_(super(ClientResource, self).get_object_list(request).all(), queryset).distinct()
        #return queryset

    def obj_get(self, request=None, **kwargs):
        if not request:
            return Clients.objects.get(**kwargs)
        return self.get_object_list(request).get(**kwargs)


    def obj_create(self, bundle, request=None, **kwargs):
        current_user = Users.objects.get(email=request.user.username)
        bundle = super(ClientResource, self).obj_create(bundle, request, id_user=current_user, id_company_type = CompanyTypes.objects.get(pk=1))
        Clients2Users.objects.create(user=current_user, client=bundle.obj)
        return bundle

    # def obj_delete(self, request=None, **kwargs):
    #     current_user = Users.objects.get(email=request.user.username)
    #     qs = current_user.clients_set.all()

    #     try:
    #         obj = qs.get(**kwargs)
    #     except ObjectDoesNotExist:
    #         raise NotFound("A model instance matching the provided arguments could not be found.")

    #     obj.delete()


    # def get_detail(self, request, **kwargs):
    #     logger.warn("get_detail request%s"%request)
    #     current_user = Users.objects.get(email=request.user.username)
    #     qs = current_user.clients_set.all()

    #     try:
    #         obj = qs.get(**self.remove_api_resource_names(kwargs))
    #     except ObjectDoesNotExist:
    #         return HttpGone()
    #     except MultipleObjectsReturned:
    #         return HttpMultipleChoices("More than one resource is found at this URI.")

    #     bundle = self.full_dehydrate(obj)
    #     bundle = self.alter_detail_data_to_serialize(request, bundle)
    #     return self.create_response(request, bundle)


    class Meta:
        queryset = Clients.objects.all()
        allowed_methods = ['get', 'post', 'put', 'delete', 'patch']
        detail_allowed_methods = ['get', 'post', 'put', 'delete', 'patch']
        resource_name = 'client'
        excludes = ['password', 'users', 'id']
        authentication = HeaderApiKeyAutentication() #ApiKeyAuthentication()
        authorization = Authorization()


class InvoiceResource(ModelResource):
    # FIXME: Invoice Rows have to be shipped too when the details are loaded
    client = fields.ForeignKey(ClientResource, 'client')
    invoicerows = fields.ToManyField('api.resources.InvoiceRowsResource', 'invoicerows_set', full=True, related_name='invoice', null=True)
    transactions = fields.ToManyField('api.resources.HiPayInvoice', 'invoicetransaction_set', full=True, related_name='invoice', null=True)
    def apply_authorization_limits(self, request, object_list):
        current_user = Users.objects.get(email=request.user.username)
        invoices = [c.invoices_set.all() for c in current_user.clients_set.all()]
        if not invoices:
            return Clients.objects.none()
        queryset = reduce(operator.or_, invoices)
        return queryset.distinct()

    # def get_detail(self, request, **kwargs):
    #     current_user = Users.objects.get(email=request.user.username)
    #     invoices = [c.invoices_set.all() for c in current_user.clients_set.all()]
    #     if not invoices:
    #         return Invoices.objects.none()
    #     qs = reduce(operator.or_, invoices)

    #     try:
    #         obj = qs.get(**self.remove_api_resource_names(kwargs))
    #     except ObjectDoesNotExist:
    #         return HttpGone()
    #     except MultipleObjectsReturned:
    #         return HttpMultipleChoices("More than one resource is found at this URI.")

    #     bundle = self.full_dehydrate(obj)
    #     bundle = self.alter_detail_data_to_serialize(request, bundle)
    #     return self.create_response(request, bundle)

    def get_object_list(self, request):
        current_user = Users.objects.get(email=request.user.username)
        invoices = [c.invoices_set.all() for c in current_user.clients_set.all()]
        if not invoices:
            return Clients.objects.none()
        queryset = reduce(operator.or_, invoices)

        return operator.and_(super(InvoiceResource, self).get_object_list(request).all(), queryset).distinct()

    def obj_get(self, request=None, **kwargs):
        if not request:
            return Invoices.objects.get(**kwargs)
        return self.get_object_list(request).get(**kwargs)


    # def obj_create(self, bundle, request=None, **kwargs):
    #     current_user = Users.objects.get(email=request.user.username)
    #     bundle = super(InvoiceResource, self).obj_create(bundle, request, id_user=current_user, id_company_type = CompanyTypes.objects.get(pk=1))
    #     logger.warn(bundle.obj)
    #     Clients2Users.objects.create(user=current_user, client=bundle.obj)
    #     return bundle


    # def override_urls(self):
    #     return [
    #         url(r"^(?P<resource_name>%s)/(?P<pk>\w[\w/-]*)/children%s$" %
    #             (self._meta.resource_name, trailing_slash()), self.wrap_view('get_children'), name="api_get_children"),
    #     ]

    # def get_children(self, request, **kwargs):
    #     try:
    #         obj = self.cached_obj_get(request=request, **self.remove_api_resource_names(kwargs))
    #     except ObjectDoesNotExist:
    #         return HttpGone()
    #     except MultipleObjectsReturned:
    #         return HttpMultipleChoices("More than one resource is found at this URI.")

    #     child_resource = ChildResource()
    #     return child_resource.get_detail(request, parent_id=obj.pk)

    class Meta:
        queryset = Invoices.objects.all()
        allowed_methods = ['get', 'post', 'put', 'delete', 'patch']
        detail_allowed_methods = ['get', 'post', 'put', 'delete', 'patch']
        excludes = ['id']
        resource_name = 'invoice'
        authentication = HeaderApiKeyAutentication() #ApiKeyAuthentication()
        authorization = Authorization()


class InvoiceRowsResource(ModelResource):
    invoice = fields.ToOneField(InvoiceResource, 'invoice', null=True)

    def apply_authorization_limits(self, request, object_list):
        if not request:
            return InvoiceRows.objects.all()
        current_user = Users.objects.get(email=request.user.username)
        invoices = [c.invoices_set.all() for c in current_user.clients_set.all()]
        if not invoices:
            return InvoiceRows.objects.none()
        queryset = reduce(operator.or_, [i.invoicerows_set.all() for i in invoices])
        return queryset.distinct()


    class Meta:
        queryset = InvoiceRows.objects.all()
        allowed_methods = ['get', 'post', 'put', 'delete', 'patch']
        detail_allowed_methods = ['get', 'post', 'put', 'delete', 'patch']
        excludes = ['id']
        resource_name = 'invoicerows'
        authentication = HeaderApiKeyAutentication() #ApiKeyAuthentication()
        authorization = Authorization()


class SubscriptionResource(ModelResource):
    client = fields.ForeignKey(ClientResource, 'client')
    subscriptionrows = fields.ToManyField('api.resources.SubscriptionRowResource', 'subscriptionrow_set', full=True, related_name='subscription', null=True)
    transactions = fields.ToManyField('api.resources.HiPaySubscription', 'subscriptiontransaction_set', full=True, related_name='subscription', null=True)
    def apply_authorization_limits(self, request, object_list):
        current_user = Users.objects.get(email=request.user.username)
        subscriptions = [c.subscription_set.all() for c in current_user.clients_set.all()]
        if not subscriptions:
            return Clients.objects.none()
        queryset = reduce(operator.or_, subscriptions)
        return queryset.distinct()


    def get_object_list(self, request):
        current_user = Users.objects.get(email=request.user.username)
        subscriptions = [c.subscription_set.all() for c in current_user.clients_set.all()]
        if not subscriptions:
            return Clients.objects.none()
        queryset = reduce(operator.or_, subscriptions)

        return operator.and_(super(SubscriptionResource, self).get_object_list(request).all(), queryset).distinct()

    def obj_get(self, request=None, **kwargs):
        if not request:
            return Subscription.objects.get(**kwargs)
        return self.get_object_list(request).get(**kwargs)

    class Meta:
        queryset = Subscription.objects.all()
        allowed_methods = ['get', 'post', 'put', 'delete', 'patch']
        detail_allowed_methods = ['get', 'post', 'put', 'delete', 'patch']
        excludes = ['id']
        resource_name = 'subscription'
        authentication = HeaderApiKeyAutentication() #ApiKeyAuthentication()
        authorization = Authorization()


class SubscriptionRowResource(ModelResource):
    subscription = fields.ToOneField(SubscriptionResource, 'subscription', null=True)

    def apply_authorization_limits(self, request, object_list):
        if not request:
            return SubscriptionRow.objects.all()
        current_user = Users.objects.get(email=request.user.username)
        subscriptions = [c.subscription_set.all() for c in current_user.clients_set.all()]
        if not subscriptions:
            return SubscriptionRow.objects.none()
        queryset = reduce(operator.or_, [i.subscriptionrow_set.all() for i in subscriptions])
        return queryset.distinct()


    class Meta:
        queryset = SubscriptionRow.objects.all()
        allowed_methods = ['get', 'post', 'put', 'delete', 'patch']
        detail_allowed_methods = ['get', 'post', 'put', 'delete', 'patch']
        excludes = ['id']
        resource_name = 'subscriptionrow'
        authentication = HeaderApiKeyAutentication() #ApiKeyAuthentication()
        authorization = Authorization()

class HiPaySubscription(ModelResource):
    subscription = fields.ToOneField(SubscriptionResource, 'subscription')

    def apply_authorization_limits(self, request, object_list):
        if not request:
            return SubscriptionTransaction.objects.all()
        current_user = Users.objects.get(email=request.user.username)
        subscriptions = reduce(operator.or_, [c.subscription_set.all() for c in current_user.clients_set.all()])
        if not subscriptions:
            return SubscriptionTransaction.objects.none()
        queryset = reduce(operator.or_, [t.subscriptiontransaction_set.all() for t in subscriptions])
        return queryset.distinct()

    class Meta:
        queryset = SubscriptionTransaction.objects.all()
        allowed_methods = ['get', 'post', 'delete']
        detail_allowed_methods = ['get', 'post', 'delete']
        excludes = ['id']
        resource_name = 'paysubscription'
        authentication = HeaderApiKeyAutentication() #ApiKeyAuthentication()
        authorization = Authorization()


    def obj_create(self, bundle, request=None, **kwargs):
        host = "http%s://%s" %('s' if request.is_secure() else '', request.get_host())
        bundle = super(HiPaySubscription, self).obj_create(bundle, request, **kwargs)
        # Go on pay it for real
        subscription = bundle.obj.subscription

        try:
            first_invoice = Invoices.objects.create(client=subscription.client,
                                                    invoice_num=subscription.ref_contrat,
                                                    period=subscription.period,
                                                    subscription=subscription)
        except Exception, e:
            logger.debug("The payment failed with exception %s " % e)
            raise ValueError("The payment failed with exception %s " % e)
            
        tr = InvoiceTransaction.objects.create(invoice=first_invoice)

        try:
            sr = subscription.subscriptionrow_set.get(first=True)
        except SubscriptionRow.DoesNotExist:
            logger.error("Malformed subscription %s, if this is a legacy subscription it should have been fixed by now: Subscription rows are: %s" % (subscription.pk, subscription.subscriptionrow_set.all()))
            raise ValueError("Malformed subscription, if this is a legacy subscription it should have been fixed by now")
        first_invoice.invoicerows_set.create(description=sr.description,
                                             qty=sr.qty,
                                             df_price=sr.price_excl_vat)
        logger.warn("Trying the payment now hold your breath ... ")
        try:
            response = simplepayment(first_invoice, sender_host=request.get_host(),
                                     secure=request.is_secure(), internal_transid=tr.pk,
                                     urls=bundle.data.get('urls', None))
        except Exception, e:
            logger.error("The payment failed with exception %s " % e)
            raise ValueError("The payment failed with exception %s " % e)

        # response = subscriptionpayment(bundle.obj.subscription, sender_host=request.get_host(),
        #                                secure=request.is_secure(), internal_transid=bundle.obj.pk, urls=bundle.data.get('urls', None))

        if response['status'] == 'Accepted':
            bundle.obj.redirect_url = response['message']
            bundle.obj.save()
            tr.redirect_url = response['message']
            tr.save()
            logger.debug("The service name should be %s" %(tr.invoice.info))
            tr.send_invoice_notice(host)
        return bundle

    def get_object_list(self, request):
        current_user = Users.objects.get(email=request.user.username)
        current_user = Users.objects.get(email=request.user.username)
        subscriptions = reduce(operator.or_, [c.subscription_set.all() for c in current_user.clients_set.all()])
        if not subscriptions:
            return SubscriptionTransaction.objects.none()
        queryset = reduce(operator.or_, [t.subscriptiontransaction_set.all() for t in subscriptions])

        return operator.and_(super(HiPaySubscription, self).get_object_list(request).all(), queryset).distinct()

    def obj_get(self, request=None, **kwargs):
        if not request:
            return SubscriptionTransaction.objects.get(**kwargs)
        return self.get_object_list(request).get(**kwargs)


class HiPayInvoice(ModelResource):
    invoice = fields.ToOneField(InvoiceResource, 'invoice')
    def apply_authorization_limits(self, request, object_list):
        if not request:
            return InvoiceTransaction.objects.all()
        current_user = Users.objects.get(email=request.user.username)
        invoices = reduce(operator.or_, [c.invoices_set.all() for c in current_user.clients_set.all()])
        if not invoices:
            return InvoiceTransaction.objects.none()
        queryset = reduce(operator.or_, [t.invoicetransaction_set.all() for t in invoices])
        return queryset.distinct()

    class Meta:
        queryset = InvoiceTransaction.objects.all()
        allowed_methods = ['post', 'get']
        detail_allowed_methods = ['post', 'get']
        excludes = ['id']
        resource_name = 'payinvoice'
        authentication = HeaderApiKeyAutentication() #ApiKeyAuthentication()
        authorization = Authorization()

    def obj_create(self, bundle, request=None, **kwargs):
        bundle = super(HiPayInvoice, self).obj_create(bundle, request, **kwargs)

        # Go on pay it for real
        response = simplepayment(bundle.obj.invoice, sender_host=request.get_host(),
                                 secure=request.is_secure(), internal_transid=bundle.obj.pk)

        if response['status'] == 'Accepted':
            bundle.obj.redirect_url = response['message']
            bundle.obj.save()

        return bundle

    def get_object_list(self, request):
        current_user = Users.objects.get(email=request.user.username)
        invoices = reduce(operator.or_, [c.invoices_set.all() for c in current_user.clients_set.all()])
        if not invoices:
            return InvoiceTransaction.objects.none()
        queryset = reduce(operator.or_, [t.invoicetransaction_set.all() for t in invoices])

        return operator.and_(super(HiPayInvoice, self).get_object_list(request).all(), queryset).distinct()

    def obj_get(self, request=None, **kwargs):
        if not request:
            return InvoiceTransaction.objects.get(**kwargs)
        return self.get_object_list(request).get(**kwargs)

