# encoding: utf-8
import datetime
from south.db import db
from south.v2 import SchemaMigration
from django.db import models

class Migration(SchemaMigration):

    def forwards(self, orm):
        
        # Adding model 'InvoiceRows'
        db.create_table(u'webfinance_invoice_rows', (
            ('id', self.gf('django.db.models.fields.AutoField')(primary_key=True, db_column='id_facture_ligne')),
            ('invoice', self.gf('django.db.models.fields.related.ForeignKey')(to=orm['invoice.Invoices'], db_column='id_facture')),
            ('description', self.gf('django.db.models.fields.TextField')(blank=True)),
            ('qty', self.gf('django.db.models.fields.DecimalField')(blank=True, null=True, db_column='qtt', decimal_places=2, max_digits=7)),
            ('order', self.gf('django.db.models.fields.IntegerField')(null=True, db_column='ordre', blank=True)),
            ('df_price', self.gf('django.db.models.fields.DecimalField')(blank=True, null=True, db_column='prix_ht', decimal_places=5, max_digits=22)),
        ))
        db.send_create_signal('invoice', ['InvoiceRows'])

        # Adding model 'SubscriptionRow'
        db.create_table(u'webfinance_subscription_rows', (
            ('id', self.gf('django.db.models.fields.AutoField')(primary_key=True)),
            ('subscription', self.gf('django.db.models.fields.related.ForeignKey')(to=orm['invoice.Subscription'])),
            ('description', self.gf('django.db.models.fields.CharField')(max_length=1024)),
            ('qty', self.gf('django.db.models.fields.DecimalField')(null=True, max_digits=5, decimal_places=2, blank=True)),
            ('price_excl_vat', self.gf('django.db.models.fields.DecimalField')(null=True, max_digits=20, decimal_places=5, blank=True)),
        ))
        db.send_create_signal('invoice', ['SubscriptionRow'])

        # Adding model 'Subscription'
        db.create_table(u'webfinance_subscription', (
            ('id', self.gf('django.db.models.fields.AutoField')(primary_key=True)),
            ('client', self.gf('django.db.models.fields.related.ForeignKey')(to=orm['enterprise.Clients'])),
            ('ref_contrat', self.gf('django.db.models.fields.CharField')(max_length=255)),
            ('period', self.gf('django.db.models.fields.CharField')(default='monthly', max_length=16)),
            ('periodic_next_deadline', self.gf('django.db.models.fields.DateField')()),
            ('delivery', self.gf('django.db.models.fields.CharField')(default='email', max_length=16)),
            ('payment_method', self.gf('django.db.models.fields.CharField')(default='unknown', max_length=16)),
            ('tax', self.gf('django.db.models.fields.DecimalField')(default='19.60', max_digits=5, decimal_places=2)),
            ('type_doc', self.gf('django.db.models.fields.CharField')(default='invoice', max_length=16)),
        ))
        db.send_create_signal('invoice', ['Subscription'])

        # Adding model 'Invoices'
        db.create_table(u'webfinance_invoices', (
            ('id', self.gf('django.db.models.fields.AutoField')(primary_key=True, db_column='id_facture')),
            ('client', self.gf('django.db.models.fields.related.ForeignKey')(to=orm['enterprise.Clients'], db_column='id_client')),
            ('date_created', self.gf('django.db.models.fields.DateTimeField')(auto_now_add=True, null=True, blank=True)),
            ('date_generated', self.gf('django.db.models.fields.DateTimeField')(null=True, blank=True)),
            ('date_sent', self.gf('django.db.models.fields.DateTimeField')(null=True, blank=True)),
            ('payment_date', self.gf('django.db.models.fields.DateTimeField')(null=True, db_column='date_paiement', blank=True)),
            ('paid', self.gf('django.db.models.fields.NullBooleanField')(default=False, null=True, db_column='is_paye', blank=True)),
            ('invoice_num', self.gf('django.db.models.fields.CharField')(unique=True, max_length=30, db_column='num_facture', blank=True)),
            ('payment_type', self.gf('django.db.models.fields.CharField')(max_length=765, db_column='type_paiement', blank=True)),
            ('ref_contract', self.gf('django.db.models.fields.CharField')(max_length=765, db_column='ref_contrat', blank=True)),
            ('extra_top', self.gf('django.db.models.fields.TextField')(blank=True)),
            ('sub_invoice', self.gf('django.db.models.fields.CharField')(max_length=765, db_column='facture_file', blank=True)),
            ('down_payment', self.gf('django.db.models.fields.DecimalField')(decimal_places=4, db_column='accompte', default='0', max_digits=12, blank=True, null=True)),
            ('extra_bottom', self.gf('django.db.models.fields.TextField')(blank=True)),
            ('invoice_date', self.gf('django.db.models.fields.DateTimeField')(null=True, db_column='date_facture', blank=True)),
            ('type_doc', self.gf('django.db.models.fields.CharField')(default='facture', max_length=27, blank=True)),
            ('comment', self.gf('django.db.models.fields.TextField')(db_column='commentaire', blank=True)),
            ('service_type', self.gf('django.db.models.fields.IntegerField')(null=True, db_column='id_type_presta', blank=True)),
            ('account', self.gf('django.db.models.fields.IntegerField')(default=30, db_column='id_compte')),
            ('sent', self.gf('django.db.models.fields.IntegerField')(null=True, db_column='is_envoye', blank=True)),
            ('period', self.gf('django.db.models.fields.CharField')(default='monthly', max_length=27, blank=True)),
            ('periodic_next_deadline', self.gf('django.db.models.fields.DateField')(null=True, blank=True)),
            ('delivery', self.gf('django.db.models.fields.CharField')(default='email', max_length=18, blank=True)),
            ('payment_method', self.gf('django.db.models.fields.CharField')(default='unknown', max_length=39, blank=True)),
            ('tax', self.gf('django.db.models.fields.DecimalField')(default='19.60', max_digits=7, decimal_places=2)),
            ('exchange_rate', self.gf('django.db.models.fields.DecimalField')(default='1.00', max_digits=10, decimal_places=2)),
        ))
        db.send_create_signal('invoice', ['Invoices'])

        # Adding model 'TypePresta'
        db.create_table(u'webfinance_type_presta', (
            ('id_type_presta', self.gf('django.db.models.fields.IntegerField')(primary_key=True)),
            ('name', self.gf('django.db.models.fields.CharField')(unique=True, max_length=255, db_column='nom', blank=True)),
        ))
        db.send_create_signal('invoice', ['TypePresta'])

        # Adding model 'Suivi'
        db.create_table(u'webfinance_suivi', (
            ('id_suivi', self.gf('django.db.models.fields.IntegerField')(primary_key=True)),
            ('type_suivi', self.gf('django.db.models.fields.IntegerField')(null=True, blank=True)),
            ('id_objet', self.gf('django.db.models.fields.IntegerField')()),
            ('message', self.gf('django.db.models.fields.TextField')(blank=True)),
            ('date_added', self.gf('django.db.models.fields.DateTimeField')(null=True, blank=True)),
            ('date_modified', self.gf('django.db.models.fields.DateTimeField')(null=True, blank=True)),
            ('added_by', self.gf('django.db.models.fields.IntegerField')(null=True, blank=True)),
            ('rappel', self.gf('django.db.models.fields.DateTimeField')(null=True, blank=True)),
            ('done', self.gf('django.db.models.fields.IntegerField')(null=True, blank=True)),
            ('done_date', self.gf('django.db.models.fields.DateTimeField')(null=True, blank=True)),
        ))
        db.send_create_signal('invoice', ['Suivi'])

        # Adding model 'TypeSuivi'
        db.create_table(u'webfinance_type_suivi', (
            ('id_type_suivi', self.gf('django.db.models.fields.IntegerField')(primary_key=True)),
            ('name', self.gf('django.db.models.fields.CharField')(max_length=600, blank=True)),
            ('selectable', self.gf('django.db.models.fields.IntegerField')(null=True, blank=True)),
        ))
        db.send_create_signal('invoice', ['TypeSuivi'])

        # Adding model 'TypeTva'
        db.create_table(u'webfinance_type_tva', (
            ('id_type_tva', self.gf('django.db.models.fields.IntegerField')(primary_key=True)),
            ('name', self.gf('django.db.models.fields.CharField')(max_length=765, db_column='nom', blank=True)),
            ('rate', self.gf('django.db.models.fields.DecimalField')(blank=True, null=True, db_column='taux', decimal_places=3, max_digits=7)),
        ))
        db.send_create_signal('invoice', ['TypeTva'])

        # Adding model 'InvoiceTransaction'
        db.create_table('invoice_invoicetransaction', (
            ('id', self.gf('django.db.models.fields.AutoField')(primary_key=True)),
            ('invoice', self.gf('django.db.models.fields.related.ForeignKey')(to=orm['invoice.Invoices'])),
            ('status', self.gf('django.db.models.fields.CharField')(max_length=255, null=True, blank=True)),
            ('emailClient', self.gf('django.db.models.fields.EmailField')(max_length=75, null=True, blank=True)),
            ('date', self.gf('django.db.models.fields.DateField')(null=True, blank=True)),
            ('operation', self.gf('django.db.models.fields.CharField')(max_length=255, null=True, blank=True)),
            ('transid', self.gf('django.db.models.fields.CharField')(max_length=255, null=True, blank=True)),
            ('merchantDatas', self.gf('django.db.models.fields.CharField')(max_length=255, null=True, blank=True)),
            ('origCurrency', self.gf('django.db.models.fields.CharField')(default='EUR', max_length=255)),
            ('origAmount', self.gf('django.db.models.fields.CharField')(max_length=255, null=True, blank=True)),
            ('idForMerchant', self.gf('django.db.models.fields.CharField')(max_length=255, null=True, blank=True)),
            ('refProduct', self.gf('django.db.models.fields.CharField')(max_length=255, null=True, blank=True)),
            ('time', self.gf('django.db.models.fields.TimeField')(null=True, blank=True)),
            ('subscriptionId', self.gf('django.db.models.fields.CharField')(max_length=255, null=True, blank=True)),
            ('not_tempered_with', self.gf('django.db.models.fields.BooleanField')(default=False)),
            ('url_ack', self.gf('django.db.models.fields.URLField')(max_length=200, null=True, blank=True)),
            ('redirect_url', self.gf('django.db.models.fields.URLField')(max_length=200, null=True, blank=True)),
            ('first_status', self.gf('django.db.models.fields.CharField')(default='pending', max_length=16)),
        ))
        db.send_create_signal('invoice', ['InvoiceTransaction'])

        # Adding model 'SubscriptionTransaction'
        db.create_table('invoice_subscriptiontransaction', (
            ('id', self.gf('django.db.models.fields.AutoField')(primary_key=True)),
            ('subscription', self.gf('django.db.models.fields.related.ForeignKey')(to=orm['invoice.Subscription'])),
            ('status', self.gf('django.db.models.fields.CharField')(max_length=255, null=True, blank=True)),
            ('emailClient', self.gf('django.db.models.fields.EmailField')(max_length=75, null=True, blank=True)),
            ('date', self.gf('django.db.models.fields.DateField')(null=True, blank=True)),
            ('operation', self.gf('django.db.models.fields.CharField')(max_length=255, null=True, blank=True)),
            ('transid', self.gf('django.db.models.fields.CharField')(max_length=255, null=True, blank=True)),
            ('merchantDatas', self.gf('django.db.models.fields.CharField')(max_length=255, null=True, blank=True)),
            ('origCurrency', self.gf('django.db.models.fields.CharField')(default='EUR', max_length=255)),
            ('origAmount', self.gf('django.db.models.fields.CharField')(max_length=255, null=True, blank=True)),
            ('idForMerchant', self.gf('django.db.models.fields.CharField')(max_length=255, null=True, blank=True)),
            ('refProduct', self.gf('django.db.models.fields.CharField')(max_length=255, null=True, blank=True)),
            ('time', self.gf('django.db.models.fields.TimeField')(null=True, blank=True)),
            ('subscriptionId', self.gf('django.db.models.fields.CharField')(max_length=255, null=True, blank=True)),
            ('not_tempered_with', self.gf('django.db.models.fields.BooleanField')(default=False)),
            ('url_ack', self.gf('django.db.models.fields.URLField')(max_length=200, null=True, blank=True)),
            ('redirect_url', self.gf('django.db.models.fields.URLField')(max_length=200, null=True, blank=True)),
            ('first_status', self.gf('django.db.models.fields.CharField')(default='pending', max_length=16)),
        ))
        db.send_create_signal('invoice', ['SubscriptionTransaction'])


    def backwards(self, orm):
        
        # Deleting model 'InvoiceRows'
        db.delete_table(u'webfinance_invoice_rows')

        # Deleting model 'SubscriptionRow'
        db.delete_table(u'webfinance_subscription_rows')

        # Deleting model 'Subscription'
        db.delete_table(u'webfinance_subscription')

        # Deleting model 'Invoices'
        db.delete_table(u'webfinance_invoices')

        # Deleting model 'TypePresta'
        db.delete_table(u'webfinance_type_presta')

        # Deleting model 'Suivi'
        db.delete_table(u'webfinance_suivi')

        # Deleting model 'TypeSuivi'
        db.delete_table(u'webfinance_type_suivi')

        # Deleting model 'TypeTva'
        db.delete_table(u'webfinance_type_tva')

        # Deleting model 'InvoiceTransaction'
        db.delete_table('invoice_invoicetransaction')

        # Deleting model 'SubscriptionTransaction'
        db.delete_table('invoice_subscriptiontransaction')


    models = {
        'enterprise.clients': {
            'Meta': {'object_name': 'Clients', 'db_table': "u'webfinance_clients'"},
            'addr1': ('django.db.models.fields.CharField', [], {'max_length': '265'}),
            'addr2': ('django.db.models.fields.CharField', [], {'max_length': '265', 'blank': 'True'}),
            'addr3': ('django.db.models.fields.CharField', [], {'max_length': '265', 'blank': 'True'}),
            'city': ('django.db.models.fields.CharField', [], {'max_length': '100', 'db_column': "'ville'"}),
            'company_type': ('django.db.models.fields.related.ForeignKey', [], {'default': '1', 'to': "orm['enterprise.CompanyTypes']", 'db_column': "'id_company_type'"}),
            'country': ('django_countries.fields.CountryField', [], {'max_length': '50', 'db_column': "'pays'"}),
            'email': ('django.db.models.fields.EmailField', [], {'max_length': '255', 'blank': 'True'}),
            'fax': ('django.db.models.fields.CharField', [], {'max_length': '200', 'blank': 'True'}),
            'id': ('django.db.models.fields.AutoField', [], {'primary_key': 'True', 'db_column': "'id_client'"}),
            'id_user': ('django.db.models.fields.related.ForeignKey', [], {'related_name': "'creator'", 'db_column': "'id_user'", 'to': "orm['enterprise.Users']"}),
            'name': ('django.db.models.fields.CharField', [], {'unique': 'True', 'max_length': '255', 'db_column': "'nom'"}),
            'password': ('django.db.models.fields.CharField', [], {'max_length': '300', 'blank': 'True'}),
            'phone': ('django.db.models.fields.CharField', [], {'max_length': '15', 'db_column': "'tel'", 'blank': 'True'}),
            'siren': ('django.db.models.fields.CharField', [], {'max_length': '50', 'blank': 'True'}),
            'users': ('django.db.models.fields.related.ManyToManyField', [], {'to': "orm['enterprise.Users']", 'through': "orm['enterprise.Clients2Users']", 'symmetrical': 'False'}),
            'vat_number': ('django.db.models.fields.CharField', [], {'max_length': '40', 'null': 'True', 'blank': 'True'}),
            'web': ('django.db.models.fields.CharField', [], {'max_length': '100', 'blank': 'True'}),
            'zip': ('django.db.models.fields.CharField', [], {'max_length': '10', 'db_column': "'cp'"})
        },
        'enterprise.clients2users': {
            'Meta': {'object_name': 'Clients2Users', 'db_table': "u'webfinance_clients2users'"},
            'client': ('django.db.models.fields.related.ForeignKey', [], {'to': "orm['enterprise.Clients']", 'db_column': "'id_client'"}),
            'id': ('django.db.models.fields.IntegerField', [], {'primary_key': 'True'}),
            'role': ('django.db.models.fields.related.ForeignKey', [], {'to': "orm['enterprise.Roles']", 'null': 'True', 'blank': 'True'}),
            'user': ('django.db.models.fields.related.ForeignKey', [], {'to': "orm['enterprise.Users']", 'db_column': "'id_user'"})
        },
        'enterprise.companytypes': {
            'Meta': {'object_name': 'CompanyTypes', 'db_table': "u'webfinance_company_types'"},
            'id': ('django.db.models.fields.IntegerField', [], {'primary_key': 'True', 'db_column': "'id_company_type'"}),
            'name': ('django.db.models.fields.CharField', [], {'max_length': '765', 'db_column': "'nom'", 'blank': 'True'})
        },
        'enterprise.roles': {
            'Meta': {'object_name': 'Roles', 'db_table': "u'webfinance_roles'"},
            'description': ('django.db.models.fields.TextField', [], {'blank': 'True'}),
            'id_role': ('django.db.models.fields.IntegerField', [], {'primary_key': 'True'}),
            'name': ('django.db.models.fields.CharField', [], {'unique': 'True', 'max_length': '60'})
        },
        'enterprise.users': {
            'Meta': {'object_name': 'Users', 'db_table': "u'webfinance_users'"},
            'creation_date': ('django.db.models.fields.DateTimeField', [], {'null': 'True', 'blank': 'True'}),
            'disabled': ('django.db.models.fields.NullBooleanField', [], {'default': 'False', 'null': 'True', 'blank': 'True'}),
            'email': ('django.db.models.fields.EmailField', [], {'unique': 'True', 'max_length': '255', 'blank': 'True'}),
            'first_name': ('django.db.models.fields.CharField', [], {'max_length': '300', 'blank': 'True'}),
            'id_user': ('django.db.models.fields.AutoField', [], {'primary_key': 'True'}),
            'last_login': ('django.db.models.fields.DateTimeField', [], {'null': 'True', 'blank': 'True'}),
            'last_name': ('django.db.models.fields.CharField', [], {'max_length': '300', 'blank': 'True'}),
            'login': ('django.db.models.fields.CharField', [], {'unique': 'True', 'max_length': '255'}),
            'modification_date': ('django.db.models.fields.DateTimeField', [], {'null': 'True', 'blank': 'True'}),
            'password': ('django.db.models.fields.CharField', [], {'max_length': '300', 'blank': 'True'}),
            'prefs': ('django.db.models.fields.TextField', [], {'blank': 'True'}),
            'role': ('django.db.models.fields.CharField', [], {'max_length': '192', 'blank': 'True'})
        },
        'invoice.invoicerows': {
            'Meta': {'object_name': 'InvoiceRows', 'db_table': "u'webfinance_invoice_rows'"},
            'description': ('django.db.models.fields.TextField', [], {'blank': 'True'}),
            'df_price': ('django.db.models.fields.DecimalField', [], {'blank': 'True', 'null': 'True', 'db_column': "'prix_ht'", 'decimal_places': '5', 'max_digits': '22'}),
            'id': ('django.db.models.fields.AutoField', [], {'primary_key': 'True', 'db_column': "'id_facture_ligne'"}),
            'invoice': ('django.db.models.fields.related.ForeignKey', [], {'to': "orm['invoice.Invoices']", 'db_column': "'id_facture'"}),
            'order': ('django.db.models.fields.IntegerField', [], {'null': 'True', 'db_column': "'ordre'", 'blank': 'True'}),
            'qty': ('django.db.models.fields.DecimalField', [], {'blank': 'True', 'null': 'True', 'db_column': "'qtt'", 'decimal_places': '2', 'max_digits': '7'})
        },
        'invoice.invoices': {
            'Meta': {'object_name': 'Invoices', 'db_table': "u'webfinance_invoices'"},
            'account': ('django.db.models.fields.IntegerField', [], {'default': '30', 'db_column': "'id_compte'"}),
            'client': ('django.db.models.fields.related.ForeignKey', [], {'to': "orm['enterprise.Clients']", 'db_column': "'id_client'"}),
            'comment': ('django.db.models.fields.TextField', [], {'db_column': "'commentaire'", 'blank': 'True'}),
            'date_created': ('django.db.models.fields.DateTimeField', [], {'auto_now_add': 'True', 'null': 'True', 'blank': 'True'}),
            'date_generated': ('django.db.models.fields.DateTimeField', [], {'null': 'True', 'blank': 'True'}),
            'date_sent': ('django.db.models.fields.DateTimeField', [], {'null': 'True', 'blank': 'True'}),
            'delivery': ('django.db.models.fields.CharField', [], {'default': "'email'", 'max_length': '18', 'blank': 'True'}),
            'down_payment': ('django.db.models.fields.DecimalField', [], {'decimal_places': '4', 'db_column': "'accompte'", 'default': "'0'", 'max_digits': '12', 'blank': 'True', 'null': 'True'}),
            'exchange_rate': ('django.db.models.fields.DecimalField', [], {'default': "'1.00'", 'max_digits': '10', 'decimal_places': '2'}),
            'extra_bottom': ('django.db.models.fields.TextField', [], {'blank': 'True'}),
            'extra_top': ('django.db.models.fields.TextField', [], {'blank': 'True'}),
            'id': ('django.db.models.fields.AutoField', [], {'primary_key': 'True', 'db_column': "'id_facture'"}),
            'invoice_date': ('django.db.models.fields.DateTimeField', [], {'null': 'True', 'db_column': "'date_facture'", 'blank': 'True'}),
            'invoice_num': ('django.db.models.fields.CharField', [], {'unique': 'True', 'max_length': '30', 'db_column': "'num_facture'", 'blank': 'True'}),
            'paid': ('django.db.models.fields.NullBooleanField', [], {'default': 'False', 'null': 'True', 'db_column': "'is_paye'", 'blank': 'True'}),
            'payment_date': ('django.db.models.fields.DateTimeField', [], {'null': 'True', 'db_column': "'date_paiement'", 'blank': 'True'}),
            'payment_method': ('django.db.models.fields.CharField', [], {'default': "'unknown'", 'max_length': '39', 'blank': 'True'}),
            'payment_type': ('django.db.models.fields.CharField', [], {'max_length': '765', 'db_column': "'type_paiement'", 'blank': 'True'}),
            'period': ('django.db.models.fields.CharField', [], {'default': "'monthly'", 'max_length': '27', 'blank': 'True'}),
            'periodic_next_deadline': ('django.db.models.fields.DateField', [], {'null': 'True', 'blank': 'True'}),
            'ref_contract': ('django.db.models.fields.CharField', [], {'max_length': '765', 'db_column': "'ref_contrat'", 'blank': 'True'}),
            'sent': ('django.db.models.fields.IntegerField', [], {'null': 'True', 'db_column': "'is_envoye'", 'blank': 'True'}),
            'service_type': ('django.db.models.fields.IntegerField', [], {'null': 'True', 'db_column': "'id_type_presta'", 'blank': 'True'}),
            'sub_invoice': ('django.db.models.fields.CharField', [], {'max_length': '765', 'db_column': "'facture_file'", 'blank': 'True'}),
            'tax': ('django.db.models.fields.DecimalField', [], {'default': "'19.60'", 'max_digits': '7', 'decimal_places': '2'}),
            'type_doc': ('django.db.models.fields.CharField', [], {'default': "'facture'", 'max_length': '27', 'blank': 'True'})
        },
        'invoice.invoicetransaction': {
            'Meta': {'object_name': 'InvoiceTransaction'},
            'date': ('django.db.models.fields.DateField', [], {'null': 'True', 'blank': 'True'}),
            'emailClient': ('django.db.models.fields.EmailField', [], {'max_length': '75', 'null': 'True', 'blank': 'True'}),
            'first_status': ('django.db.models.fields.CharField', [], {'default': "'pending'", 'max_length': '16'}),
            'id': ('django.db.models.fields.AutoField', [], {'primary_key': 'True'}),
            'idForMerchant': ('django.db.models.fields.CharField', [], {'max_length': '255', 'null': 'True', 'blank': 'True'}),
            'invoice': ('django.db.models.fields.related.ForeignKey', [], {'to': "orm['invoice.Invoices']"}),
            'merchantDatas': ('django.db.models.fields.CharField', [], {'max_length': '255', 'null': 'True', 'blank': 'True'}),
            'not_tempered_with': ('django.db.models.fields.BooleanField', [], {'default': 'False'}),
            'operation': ('django.db.models.fields.CharField', [], {'max_length': '255', 'null': 'True', 'blank': 'True'}),
            'origAmount': ('django.db.models.fields.CharField', [], {'max_length': '255', 'null': 'True', 'blank': 'True'}),
            'origCurrency': ('django.db.models.fields.CharField', [], {'default': "'EUR'", 'max_length': '255'}),
            'redirect_url': ('django.db.models.fields.URLField', [], {'max_length': '200', 'null': 'True', 'blank': 'True'}),
            'refProduct': ('django.db.models.fields.CharField', [], {'max_length': '255', 'null': 'True', 'blank': 'True'}),
            'status': ('django.db.models.fields.CharField', [], {'max_length': '255', 'null': 'True', 'blank': 'True'}),
            'subscriptionId': ('django.db.models.fields.CharField', [], {'max_length': '255', 'null': 'True', 'blank': 'True'}),
            'time': ('django.db.models.fields.TimeField', [], {'null': 'True', 'blank': 'True'}),
            'transid': ('django.db.models.fields.CharField', [], {'max_length': '255', 'null': 'True', 'blank': 'True'}),
            'url_ack': ('django.db.models.fields.URLField', [], {'max_length': '200', 'null': 'True', 'blank': 'True'})
        },
        'invoice.subscription': {
            'Meta': {'object_name': 'Subscription', 'db_table': "u'webfinance_subscription'"},
            'client': ('django.db.models.fields.related.ForeignKey', [], {'to': "orm['enterprise.Clients']"}),
            'delivery': ('django.db.models.fields.CharField', [], {'default': "'email'", 'max_length': '16'}),
            'id': ('django.db.models.fields.AutoField', [], {'primary_key': 'True'}),
            'payment_method': ('django.db.models.fields.CharField', [], {'default': "'unknown'", 'max_length': '16'}),
            'period': ('django.db.models.fields.CharField', [], {'default': "'monthly'", 'max_length': '16'}),
            'periodic_next_deadline': ('django.db.models.fields.DateField', [], {}),
            'ref_contrat': ('django.db.models.fields.CharField', [], {'max_length': '255'}),
            'tax': ('django.db.models.fields.DecimalField', [], {'default': "'19.60'", 'max_digits': '5', 'decimal_places': '2'}),
            'type_doc': ('django.db.models.fields.CharField', [], {'default': "'invoice'", 'max_length': '16'})
        },
        'invoice.subscriptionrow': {
            'Meta': {'object_name': 'SubscriptionRow', 'db_table': "u'webfinance_subscription_rows'"},
            'description': ('django.db.models.fields.CharField', [], {'max_length': '1024'}),
            'id': ('django.db.models.fields.AutoField', [], {'primary_key': 'True'}),
            'price_excl_vat': ('django.db.models.fields.DecimalField', [], {'null': 'True', 'max_digits': '20', 'decimal_places': '5', 'blank': 'True'}),
            'qty': ('django.db.models.fields.DecimalField', [], {'null': 'True', 'max_digits': '5', 'decimal_places': '2', 'blank': 'True'}),
            'subscription': ('django.db.models.fields.related.ForeignKey', [], {'to': "orm['invoice.Subscription']"})
        },
        'invoice.subscriptiontransaction': {
            'Meta': {'object_name': 'SubscriptionTransaction'},
            'date': ('django.db.models.fields.DateField', [], {'null': 'True', 'blank': 'True'}),
            'emailClient': ('django.db.models.fields.EmailField', [], {'max_length': '75', 'null': 'True', 'blank': 'True'}),
            'first_status': ('django.db.models.fields.CharField', [], {'default': "'pending'", 'max_length': '16'}),
            'id': ('django.db.models.fields.AutoField', [], {'primary_key': 'True'}),
            'idForMerchant': ('django.db.models.fields.CharField', [], {'max_length': '255', 'null': 'True', 'blank': 'True'}),
            'merchantDatas': ('django.db.models.fields.CharField', [], {'max_length': '255', 'null': 'True', 'blank': 'True'}),
            'not_tempered_with': ('django.db.models.fields.BooleanField', [], {'default': 'False'}),
            'operation': ('django.db.models.fields.CharField', [], {'max_length': '255', 'null': 'True', 'blank': 'True'}),
            'origAmount': ('django.db.models.fields.CharField', [], {'max_length': '255', 'null': 'True', 'blank': 'True'}),
            'origCurrency': ('django.db.models.fields.CharField', [], {'default': "'EUR'", 'max_length': '255'}),
            'redirect_url': ('django.db.models.fields.URLField', [], {'max_length': '200', 'null': 'True', 'blank': 'True'}),
            'refProduct': ('django.db.models.fields.CharField', [], {'max_length': '255', 'null': 'True', 'blank': 'True'}),
            'status': ('django.db.models.fields.CharField', [], {'max_length': '255', 'null': 'True', 'blank': 'True'}),
            'subscription': ('django.db.models.fields.related.ForeignKey', [], {'to': "orm['invoice.Subscription']"}),
            'subscriptionId': ('django.db.models.fields.CharField', [], {'max_length': '255', 'null': 'True', 'blank': 'True'}),
            'time': ('django.db.models.fields.TimeField', [], {'null': 'True', 'blank': 'True'}),
            'transid': ('django.db.models.fields.CharField', [], {'max_length': '255', 'null': 'True', 'blank': 'True'}),
            'url_ack': ('django.db.models.fields.URLField', [], {'max_length': '200', 'null': 'True', 'blank': 'True'})
        },
        'invoice.suivi': {
            'Meta': {'object_name': 'Suivi', 'db_table': "u'webfinance_suivi'"},
            'added_by': ('django.db.models.fields.IntegerField', [], {'null': 'True', 'blank': 'True'}),
            'date_added': ('django.db.models.fields.DateTimeField', [], {'null': 'True', 'blank': 'True'}),
            'date_modified': ('django.db.models.fields.DateTimeField', [], {'null': 'True', 'blank': 'True'}),
            'done': ('django.db.models.fields.IntegerField', [], {'null': 'True', 'blank': 'True'}),
            'done_date': ('django.db.models.fields.DateTimeField', [], {'null': 'True', 'blank': 'True'}),
            'id_objet': ('django.db.models.fields.IntegerField', [], {}),
            'id_suivi': ('django.db.models.fields.IntegerField', [], {'primary_key': 'True'}),
            'message': ('django.db.models.fields.TextField', [], {'blank': 'True'}),
            'rappel': ('django.db.models.fields.DateTimeField', [], {'null': 'True', 'blank': 'True'}),
            'type_suivi': ('django.db.models.fields.IntegerField', [], {'null': 'True', 'blank': 'True'})
        },
        'invoice.typepresta': {
            'Meta': {'object_name': 'TypePresta', 'db_table': "u'webfinance_type_presta'"},
            'id_type_presta': ('django.db.models.fields.IntegerField', [], {'primary_key': 'True'}),
            'name': ('django.db.models.fields.CharField', [], {'unique': 'True', 'max_length': '255', 'db_column': "'nom'", 'blank': 'True'})
        },
        'invoice.typesuivi': {
            'Meta': {'object_name': 'TypeSuivi', 'db_table': "u'webfinance_type_suivi'"},
            'id_type_suivi': ('django.db.models.fields.IntegerField', [], {'primary_key': 'True'}),
            'name': ('django.db.models.fields.CharField', [], {'max_length': '600', 'blank': 'True'}),
            'selectable': ('django.db.models.fields.IntegerField', [], {'null': 'True', 'blank': 'True'})
        },
        'invoice.typetva': {
            'Meta': {'object_name': 'TypeTva', 'db_table': "u'webfinance_type_tva'"},
            'id_type_tva': ('django.db.models.fields.IntegerField', [], {'primary_key': 'True'}),
            'name': ('django.db.models.fields.CharField', [], {'max_length': '765', 'db_column': "'nom'", 'blank': 'True'}),
            'rate': ('django.db.models.fields.DecimalField', [], {'blank': 'True', 'null': 'True', 'db_column': "'taux'", 'decimal_places': '3', 'max_digits': '7'})
        }
    }

    complete_apps = ['invoice']
