# encoding: utf-8
import datetime
from south.db import db
from south.v2 import SchemaMigration
from django.db import models

class Migration(SchemaMigration):

    def forwards(self, orm):
        
        # Adding model 'Users'
        db.create_table(u'webfinance_users', (
            ('id_user', self.gf('django.db.models.fields.AutoField')(primary_key=True)),
            ('last_name', self.gf('django.db.models.fields.CharField')(max_length=300, blank=True)),
            ('first_name', self.gf('django.db.models.fields.CharField')(max_length=300, blank=True)),
            ('login', self.gf('django.db.models.fields.CharField')(unique=True, max_length=255)),
            ('password', self.gf('django.db.models.fields.CharField')(max_length=300, blank=True)),
            ('email', self.gf('django.db.models.fields.EmailField')(unique=True, max_length=255, blank=True)),
            ('disabled', self.gf('django.db.models.fields.NullBooleanField')(default=False, null=True, blank=True)),
            ('last_login', self.gf('django.db.models.fields.DateTimeField')(null=True, blank=True)),
            ('creation_date', self.gf('django.db.models.fields.DateTimeField')(null=True, blank=True)),
            ('role', self.gf('django.db.models.fields.CharField')(max_length=192, blank=True)),
            ('modification_date', self.gf('django.db.models.fields.DateTimeField')(null=True, blank=True)),
            ('prefs', self.gf('django.db.models.fields.TextField')(blank=True)),
        ))
        db.send_create_signal('enterprise', ['Users'])

        # Adding model 'Clients'
        db.create_table(u'webfinance_clients', (
            ('id', self.gf('django.db.models.fields.AutoField')(primary_key=True, db_column='id_client')),
            ('name', self.gf('django.db.models.fields.CharField')(unique=True, max_length=255, db_column='nom')),
            ('zip', self.gf('django.db.models.fields.CharField')(max_length=10, db_column='cp')),
            ('city', self.gf('django.db.models.fields.CharField')(max_length=100, db_column='ville')),
            ('addr1', self.gf('django.db.models.fields.CharField')(max_length=265)),
            ('addr2', self.gf('django.db.models.fields.CharField')(max_length=265, blank=True)),
            ('addr3', self.gf('django.db.models.fields.CharField')(max_length=265, blank=True)),
            ('country', self.gf('django_countries.fields.CountryField')(max_length=50, db_column='pays')),
            ('phone', self.gf('django.db.models.fields.CharField')(max_length=15, db_column='tel', blank=True)),
            ('fax', self.gf('django.db.models.fields.CharField')(max_length=200, blank=True)),
            ('web', self.gf('django.db.models.fields.CharField')(max_length=100, blank=True)),
            ('vat_number', self.gf('django.db.models.fields.CharField')(max_length=40, null=True, blank=True)),
            ('email', self.gf('django.db.models.fields.EmailField')(max_length=255, blank=True)),
            ('siren', self.gf('django.db.models.fields.CharField')(max_length=50, blank=True)),
            ('company_type', self.gf('django.db.models.fields.related.ForeignKey')(default=1, to=orm['enterprise.CompanyTypes'], db_column='id_company_type')),
            ('id_user', self.gf('django.db.models.fields.related.ForeignKey')(related_name='creator', db_column='id_user', to=orm['enterprise.Users'])),
            ('password', self.gf('django.db.models.fields.CharField')(max_length=300, blank=True)),
        ))
        db.send_create_signal('enterprise', ['Clients'])

        # Adding model 'Clients2Users'
        db.create_table(u'webfinance_clients2users', (
            ('id', self.gf('django.db.models.fields.IntegerField')(primary_key=True)),
            ('client', self.gf('django.db.models.fields.related.ForeignKey')(to=orm['enterprise.Clients'], db_column='id_client')),
            ('user', self.gf('django.db.models.fields.related.ForeignKey')(to=orm['enterprise.Users'], db_column='id_user')),
            ('role', self.gf('django.db.models.fields.related.ForeignKey')(to=orm['enterprise.Roles'], null=True, blank=True)),
        ))
        db.send_create_signal('enterprise', ['Clients2Users'])

        # Adding model 'CompanyTypes'
        db.create_table(u'webfinance_company_types', (
            ('id', self.gf('django.db.models.fields.IntegerField')(primary_key=True, db_column='id_company_type')),
            ('name', self.gf('django.db.models.fields.CharField')(max_length=765, db_column='nom', blank=True)),
        ))
        db.send_create_signal('enterprise', ['CompanyTypes'])

        # Adding model 'Userlog'
        db.create_table(u'webfinance_userlog', (
            ('id_userlog', self.gf('django.db.models.fields.IntegerField')(primary_key=True)),
            ('log', self.gf('django.db.models.fields.TextField')(blank=True)),
            ('date', self.gf('django.db.models.fields.DateTimeField')(null=True, blank=True)),
            ('id_user', self.gf('django.db.models.fields.related.ForeignKey')(to=orm['enterprise.Users'], db_column='id_user')),
            ('id_facture', self.gf('django.db.models.fields.IntegerField')(null=True, blank=True)),
            ('id_client', self.gf('django.db.models.fields.IntegerField')(null=True, blank=True)),
        ))
        db.send_create_signal('enterprise', ['Userlog'])

        # Adding model 'Personne'
        db.create_table(u'webfinance_personne', (
            ('id_personne', self.gf('django.db.models.fields.IntegerField')(primary_key=True)),
            ('nom', self.gf('django.db.models.fields.CharField')(max_length=300, blank=True)),
            ('prenom', self.gf('django.db.models.fields.CharField')(max_length=300, blank=True)),
            ('date_created', self.gf('django.db.models.fields.DateTimeField')(null=True, blank=True)),
            ('entreprise', self.gf('django.db.models.fields.CharField')(max_length=90, blank=True)),
            ('fonction', self.gf('django.db.models.fields.CharField')(max_length=90, blank=True)),
            ('tel', self.gf('django.db.models.fields.CharField')(max_length=45, blank=True)),
            ('tel_perso', self.gf('django.db.models.fields.CharField')(max_length=45, blank=True)),
            ('mobile', self.gf('django.db.models.fields.CharField')(max_length=45, blank=True)),
            ('fax', self.gf('django.db.models.fields.CharField')(max_length=45, blank=True)),
            ('email', self.gf('django.db.models.fields.CharField')(max_length=765, blank=True)),
            ('adresse1', self.gf('django.db.models.fields.CharField')(max_length=765, blank=True)),
            ('ville', self.gf('django.db.models.fields.CharField')(max_length=765, blank=True)),
            ('cp', self.gf('django.db.models.fields.CharField')(max_length=30, blank=True)),
            ('digicode', self.gf('django.db.models.fields.CharField')(max_length=30, blank=True)),
            ('station_metro', self.gf('django.db.models.fields.CharField')(max_length=30, blank=True)),
            ('date_anniversaire', self.gf('django.db.models.fields.CharField')(max_length=30, blank=True)),
            ('note', self.gf('django.db.models.fields.TextField')(blank=True)),
            ('client', self.gf('django.db.models.fields.related.ForeignKey')(to=orm['enterprise.Clients'], db_column='client')),
        ))
        db.send_create_signal('enterprise', ['Personne'])

        # Adding model 'Roles'
        db.create_table(u'webfinance_roles', (
            ('id_role', self.gf('django.db.models.fields.IntegerField')(primary_key=True)),
            ('name', self.gf('django.db.models.fields.CharField')(unique=True, max_length=60)),
            ('description', self.gf('django.db.models.fields.TextField')(blank=True)),
        ))
        db.send_create_signal('enterprise', ['Roles'])

        # Adding model 'Invitation'
        db.create_table('enterprise_invitation', (
            ('id', self.gf('django.db.models.fields.AutoField')(primary_key=True)),
            ('token', self.gf('django.db.models.fields.CharField')(max_length=255)),
            ('revocation_token', self.gf('django.db.models.fields.CharField')(max_length=255)),
            ('email', self.gf('django.db.models.fields.EmailField')(max_length=75)),
            ('first_name', self.gf('django.db.models.fields.CharField')(max_length=255)),
            ('last_name', self.gf('django.db.models.fields.CharField')(max_length=255)),
            ('company', self.gf('django.db.models.fields.related.ForeignKey')(to=orm['enterprise.Clients'])),
            ('guest', self.gf('django.db.models.fields.related.ForeignKey')(to=orm['enterprise.Users'])),
            ('accepted', self.gf('django.db.models.fields.BooleanField')(default=False)),
            ('revoked', self.gf('django.db.models.fields.BooleanField')(default=False)),
            ('acceptation_date', self.gf('django.db.models.fields.DateTimeField')(null=True, blank=True)),
            ('revocation_date', self.gf('django.db.models.fields.DateTimeField')(null=True, blank=True)),
        ))
        db.send_create_signal('enterprise', ['Invitation'])

        # Adding unique constraint on 'Invitation', fields ['email', 'company', 'guest']
        db.create_unique('enterprise_invitation', ['email', 'company_id', 'guest_id'])


    def backwards(self, orm):
        
        # Removing unique constraint on 'Invitation', fields ['email', 'company', 'guest']
        db.delete_unique('enterprise_invitation', ['email', 'company_id', 'guest_id'])

        # Deleting model 'Users'
        db.delete_table(u'webfinance_users')

        # Deleting model 'Clients'
        db.delete_table(u'webfinance_clients')

        # Deleting model 'Clients2Users'
        db.delete_table(u'webfinance_clients2users')

        # Deleting model 'CompanyTypes'
        db.delete_table(u'webfinance_company_types')

        # Deleting model 'Userlog'
        db.delete_table(u'webfinance_userlog')

        # Deleting model 'Personne'
        db.delete_table(u'webfinance_personne')

        # Deleting model 'Roles'
        db.delete_table(u'webfinance_roles')

        # Deleting model 'Invitation'
        db.delete_table('enterprise_invitation')


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
        'enterprise.invitation': {
            'Meta': {'unique_together': "(('email', 'company', 'guest'),)", 'object_name': 'Invitation'},
            'acceptation_date': ('django.db.models.fields.DateTimeField', [], {'null': 'True', 'blank': 'True'}),
            'accepted': ('django.db.models.fields.BooleanField', [], {'default': 'False'}),
            'company': ('django.db.models.fields.related.ForeignKey', [], {'to': "orm['enterprise.Clients']"}),
            'email': ('django.db.models.fields.EmailField', [], {'max_length': '75'}),
            'first_name': ('django.db.models.fields.CharField', [], {'max_length': '255'}),
            'guest': ('django.db.models.fields.related.ForeignKey', [], {'to': "orm['enterprise.Users']"}),
            'id': ('django.db.models.fields.AutoField', [], {'primary_key': 'True'}),
            'last_name': ('django.db.models.fields.CharField', [], {'max_length': '255'}),
            'revocation_date': ('django.db.models.fields.DateTimeField', [], {'null': 'True', 'blank': 'True'}),
            'revocation_token': ('django.db.models.fields.CharField', [], {'max_length': '255'}),
            'revoked': ('django.db.models.fields.BooleanField', [], {'default': 'False'}),
            'token': ('django.db.models.fields.CharField', [], {'max_length': '255'})
        },
        'enterprise.personne': {
            'Meta': {'object_name': 'Personne', 'db_table': "u'webfinance_personne'"},
            'adresse1': ('django.db.models.fields.CharField', [], {'max_length': '765', 'blank': 'True'}),
            'client': ('django.db.models.fields.related.ForeignKey', [], {'to': "orm['enterprise.Clients']", 'db_column': "'client'"}),
            'cp': ('django.db.models.fields.CharField', [], {'max_length': '30', 'blank': 'True'}),
            'date_anniversaire': ('django.db.models.fields.CharField', [], {'max_length': '30', 'blank': 'True'}),
            'date_created': ('django.db.models.fields.DateTimeField', [], {'null': 'True', 'blank': 'True'}),
            'digicode': ('django.db.models.fields.CharField', [], {'max_length': '30', 'blank': 'True'}),
            'email': ('django.db.models.fields.CharField', [], {'max_length': '765', 'blank': 'True'}),
            'entreprise': ('django.db.models.fields.CharField', [], {'max_length': '90', 'blank': 'True'}),
            'fax': ('django.db.models.fields.CharField', [], {'max_length': '45', 'blank': 'True'}),
            'fonction': ('django.db.models.fields.CharField', [], {'max_length': '90', 'blank': 'True'}),
            'id_personne': ('django.db.models.fields.IntegerField', [], {'primary_key': 'True'}),
            'mobile': ('django.db.models.fields.CharField', [], {'max_length': '45', 'blank': 'True'}),
            'nom': ('django.db.models.fields.CharField', [], {'max_length': '300', 'blank': 'True'}),
            'note': ('django.db.models.fields.TextField', [], {'blank': 'True'}),
            'prenom': ('django.db.models.fields.CharField', [], {'max_length': '300', 'blank': 'True'}),
            'station_metro': ('django.db.models.fields.CharField', [], {'max_length': '30', 'blank': 'True'}),
            'tel': ('django.db.models.fields.CharField', [], {'max_length': '45', 'blank': 'True'}),
            'tel_perso': ('django.db.models.fields.CharField', [], {'max_length': '45', 'blank': 'True'}),
            'ville': ('django.db.models.fields.CharField', [], {'max_length': '765', 'blank': 'True'})
        },
        'enterprise.roles': {
            'Meta': {'object_name': 'Roles', 'db_table': "u'webfinance_roles'"},
            'description': ('django.db.models.fields.TextField', [], {'blank': 'True'}),
            'id_role': ('django.db.models.fields.IntegerField', [], {'primary_key': 'True'}),
            'name': ('django.db.models.fields.CharField', [], {'unique': 'True', 'max_length': '60'})
        },
        'enterprise.userlog': {
            'Meta': {'object_name': 'Userlog', 'db_table': "u'webfinance_userlog'"},
            'date': ('django.db.models.fields.DateTimeField', [], {'null': 'True', 'blank': 'True'}),
            'id_client': ('django.db.models.fields.IntegerField', [], {'null': 'True', 'blank': 'True'}),
            'id_facture': ('django.db.models.fields.IntegerField', [], {'null': 'True', 'blank': 'True'}),
            'id_user': ('django.db.models.fields.related.ForeignKey', [], {'to': "orm['enterprise.Users']", 'db_column': "'id_user'"}),
            'id_userlog': ('django.db.models.fields.IntegerField', [], {'primary_key': 'True'}),
            'log': ('django.db.models.fields.TextField', [], {'blank': 'True'})
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
        }
    }

    complete_apps = ['enterprise']
