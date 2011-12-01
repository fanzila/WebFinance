#!/usr/bin/env python
#-*- coding: utf-8 -*-
#Copyright (C) 2011 ISVTEC SARL
#$Id$
__author__ = "Ousmane Wilane ♟ <ousmane@wilane.org>"
__date__   = "Sat Nov 26 12:12:29 2011"


from tastypie.resources import ModelResource
from fo.invoice.models import Invoices, Clients, InvoiceRows, Subscription, SubscriptionRow
from fo.enterprise.models import Users, Clients2Users, CompanyTypes
from tastypie import fields
from tastypie.authentication import ApiKeyAuthentication
from tastypie.utils.urls import trailing_slash
from tastypie.authorization import DjangoAuthorization, Authorization
from django.core.exceptions import ObjectDoesNotExist, MultipleObjectsReturned
from django.conf.urls.defaults import url
from tastypie.http import *
import operator
import logging
logger = logging.getLogger('django')

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
        excludes = ['password', 'users']
        authentication = ApiKeyAuthentication()
        authorization = Authorization()


class InvoiceResource(ModelResource):
    # FIXME: Invoice Rows have to be shipped too when the details are loaded
    client = fields.ForeignKey(ClientResource, 'client')
    invoicerows = fields.ToManyField('fo.api.resources.InvoiceRowsResource', 'invoicerows_set', full=True, related_name='id_facture', null=True)
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
        resource_name = 'invoice'
        authentication = ApiKeyAuthentication()
        authorization = Authorization()


class InvoiceRowsResource(ModelResource):
    id_facture = fields.ToOneField(InvoiceResource, 'id_facture', null=True)
    
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
        resource_name = 'invoicerows'
        authentication = ApiKeyAuthentication()
        authorization = Authorization()


class SubscriptionResource(ModelResource):
    client = fields.ForeignKey(ClientResource, 'client')
    subscriptionrows = fields.ToManyField('fo.api.resources.SubscriptionRowResource', 'subscriptionrow_set', full=True, related_name='subscription', null=True)
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
        resource_name = 'subscription'
        authentication = ApiKeyAuthentication()
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
        resource_name = 'subscriptionrow'
        authentication = ApiKeyAuthentication()
        authorization = Authorization()
