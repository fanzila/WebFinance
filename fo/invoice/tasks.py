#!/usr/bin/env python
#-*- coding: utf-8 -*-
#Copyright (C) 2011 ISVTEC SARL
#$Id$
__author__ = "Ousmane Wilane ♟ <ousmane@wilane.org>"
__date__   = "Mon Feb 20 18:28:44 2012"

import urllib2
from urllib import urlencode
from invoice.models import Subscription, InvoiceTransaction, Invoices
#from celery.decorators import task
from celery.task import task
from django.core import serializers
from celery.signals import task_postrun
from datetime import datetime
import logging
logger = logging.getLogger('isvtec')

@task(track_started=True)
def deactivate_subscription(subscription_id=None):
    sub = Subscription.objects.get(pk=subscription_id)
    sub.status = 'expired'
    sub.save()
    ipn_subscription.delay(subscription_id)

@task(max_retries=288, default_retry_delay=5*60,
      store_errors_even_if_ignored=True,
      send_error_emails=True)
def ipn_subscription(subscription_id=None, update_type=None, invoice_id=None):
    # FIXME: use requests api, it's way cooler than this voodoo sorry

    sub = Subscription.objects.get(pk=subscription_id)
    status_url = sub.status_url

    if invoice_id:
        inv = Invoices.objects.get(pk=invoice_id)
        if inv.status_url:
            # Override this
            status_url = inv.status_url

    logger.info("Pinging %s for IPN subscription state change" % status_url)
    data = serializers.serialize("json", Subscription.objects.filter(pk=subscription_id))
    opener = urllib2.build_opener()
    opener.addheaders = [("Content-Type", "text/json"),
                         ("Content-Length", str(len(data))),
                         ("User-Agent", u"ISVTEC -- PAYMENT GATEWAY")]
    urllib2.install_opener(opener)
    request = urllib2.Request(sub.status_url,urlencode({'subscription':data, 'update_type':update_type}))
    try:
        response = opener.open(request)
        message = u"Notified %s ... propagation, got '%s'" %(status_url, response.read())
        logger.info(message)
    except Exception, e:
        message = u"Unable to notify %s, we have a status change to annouce: %s" %(status_url, e)
        logger.warn(message)
        ipn_subscription.retry(exc=e)

@task(track_started=True)
def subscription_reminder(subscription_id=None):
    """Run as a cron to remind people for subscriptions that will expire soon
    60, 30, 15, 7, 3, 1 days before"""
    # Mark subscription that are canceled and avoid them here
    subs = Subscription.objects.count()
    for sub in Subscription.objects.all():
        if sub.expiration_date and sub.status == 'running':
            days_left = (sub.expiration_date - datetime.now()).days
            if days_left in (60, 30, 15, 7, 3, 1):
                sub.send_reminder()
                sub.reminder_sent_date = datetime.now()
            # The subscription will expire before our next run
            if days_left < 1:
                # schedule the task that will mark the subscription as expired
                # and ping the app that manage the service
                if sub.expiration_date <= datetime.now():
                    deactivate_subscription.delay(args=[sub.pk])
                else:
                    deactivate_subscription.apply_async(args=[sub.pk], eta=sub.expiration_date)

        subscription_reminder.update_state(state="PROGRESS",
                                           meta={"current": sub.pk, "total": subs})

    return True

@task(max_retries=288, default_retry_delay=5*60,
      store_errors_even_if_ignored=True,
      send_error_emails=True)
def ipn_ping(tr_id):
    # FIXME: Use the requests api
    worker_logger = ipn_ping.get_logger()
    trans = InvoiceTransaction.objects.get(pk=tr_id)
    logger.info("Pinging %s for IPN from upstream" % trans.url_ack)
    data = serializers.serialize("json", InvoiceTransaction.objects.filter(pk=tr_id))
    opener = urllib2.build_opener()
    opener.addheaders = [("Content-Type", "text/json"),
                         ("Content-Length", str(len(data))),
                         ("User-Agent", u"ISVTEC -- PAYMENT GATEWAY")]
    urllib2.install_opener(opener)
    request = urllib2.Request(trans.url_ack,urlencode({'payment':data}))
    try:
        response = opener.open(request)
        message = u"Pinged back %s ... propagation, got '%s'" %(trans.url_ack, response.read())
        logger.info(message)
        worker_logger.info(message)
    except Exception, e:
        message = u"Unable to ping back %s, we have an ack to propage: %s" %(trans.url_ack, e)
        logger.warn(message)
        worker_logger.info(message)
        ipn_ping.retry(exc=e)

def reminder_postrun_handler(sender=None, task_id=None, task=None, args=None, kwargs=None, **kwds):
    logger.error("Got signal task_postrun for task id %s and task %s" % (task_id, task.name))

task_postrun.connect(reminder_postrun_handler) #, sender="invoice.tasks.subscription_reminder")
