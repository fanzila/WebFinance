#!/usr/bin/env python
#-*- coding: utf-8 -*-
#Copyright (C) 2011 ISVTEC SARL
#$Id$
__author__ = "Ousmane Wilane ♟ <ousmane@wilane.org>"
__date__   = "Fri Nov 11 11:01:48 2011"

from django.conf.urls.defaults import patterns, url

urlpatterns = patterns('',
                       url(r'^companies$', 'invoice.views.list_companies', name='list_companies'),
                       url(r'^list/(?P<customer_id>\d+)$', 'invoice.views.list_invoices', name='list_invoices'),

                       url(r'^show/invoice/(?P<invoice_id>\d+)$', 'invoice.views.detail_invoice', name='detail_invoice'),
                       url(r'^accept/invoice/(?P<invoice_id>\d+)$', 'invoice.views.accept_quote', name='accept_quote'),
                       url(r'^download/invoice/(?P<invoice_id>\d+)$', 'invoice.views.download_invoice', name='download_invoice'),

                       url(r'^show/subscription/(?P<subscription_id>\d+)$', 'invoice.views.detail_subscription', name='detail_subscription'),
                       url(r'^accept/subscription/(?P<subscription_id>\d+)$', 'invoice.views.accept_subscriptionquote', name='accept_subscriptionquote'),
                       url(r'^download/subscription/(?P<subscription_id>\d+)$', 'invoice.views.download_subscription', name='download_subscription'),

                       url(r'^hipay/invoice/(?P<invoice_id>\d+)$', 'invoice.views.hipay_invoice', name='hipay_invoice'),
                       url(r'^hipay/subscription/(?P<subscription_id>\d+)$', 'invoice.views.hipay_subscription', name='hipay_paysubs'),
                       url(r'^subscription/invoices/(?P<subscription_id>\d+)$', 'invoice.views.subscription_invoices', name='subscription_invoices'),

                       url(r'^hipay/payment/(?P<payment_type>invoice|subscription)/(?P<action>cancel|ok|nook)/(?P<invoice_id>\d+)/(?P<internal_transid>\d+)$', 'invoice.views.hipay_payment_url', name='hipay_payment_url'),
                       url(r'^hipay/result/ack/(?P<payment_type>invoice|subscription)/(?P<invoice_id>\d+)/(?P<internal_transid>\d+)$', 'invoice.views.hipay_ipn_ack', name='hipay_ipn_ack'),
                       url(r'^hipay/shop/logo$', 'invoice.views.hipay_shop_logo', name='hipay_shop_logo'),
                       url(r'^hipay/reack$', 'invoice.views.ack_postback', name='ack_postback'),
                       url(r'^subscription/postback$', 'invoice.views.sub_status_postback', name='sub_status_postback'),
                       url(r'^order/(?P<order_id>[-aA-zZ0-9]+)$', 'invoice.views.checkout', name='checkout'),


                       url(r'^hipay/test_url_ack$', 'invoice.views.test_url_ack', name='test_url_ack'),
                       url(r'^renew/subscription/(?P<subscription_id>\d+)$', 'invoice.views.renew_subscription', name='renew_subscription'),
)
