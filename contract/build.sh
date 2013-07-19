#!/bin/bash
#
# Copyright (C) 2013 Cyril Bouthors <cyril@boutho.rs>
#
# This program is free software: you can redistribute it and/or modify it under
# the terms of the GNU General Public License as published by the Free Software
# Foundation, either version 3 of the License, or (at your option) any later
# version.
#
# This program is distributed in the hope that it will be useful, but WITHOUT
# ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
# FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License along with
# this program. If not, see <http://www.gnu.org/licenses/>.
#

set -e -o pipefail -o nounset

usage() {
    echo "$0: Too few argument!" >&2
    echo "Usage: $0" >&2
    echo "--template-file=TEMPLATE_FILE" >&2
    echo "--output=OUTPUT_FILE" >&2
    echo "--company=COMPANY" >&2
    echo "--business_entity=BUSINESS_ENTITY" >&2
    echo "--contract_signer_role=SIGNER_ROLE" >&2
    echo "--contract_signer=SIGNER" >&2
    echo "--capital=CAPITAL" >&2
    echo "--date=DATE" >&2
    echo "--address=ADDRESS" >&2
}

# ":" means required
# "::" means optional
TEMP=$(getopt -o h: --long template-file:,output:,business_entity:,rcs: \
    --long=company:,contract_signer_role:,contract_signer:,capital:,address: \
    --long=date: -n "$0" -- "$@")

# Check for non-GNU getopt
if [ $? != 0 ]
then
    usage
    exit 1
fi

# Parse options with getopt
eval set -- "$TEMP"
while true
do
    case "$1" in
	--template-file)
	    template_file="$2"
	    shift 2
	    ;;

	--output)
	    output="$2"
	    shift 2
	    ;;

	--company)
	    company="$2"
	    shift 2
	    ;;

	--business_entity)
	    business_entity="$2"
	    shift 2
	    ;;

	--rcs)
	    rcs="$2"
	    shift 2
	    ;;

	--contract_signer_role)
	    contract_signer_role="$2"
	    shift 2
	    ;;

	--contract_signer)
	    contract_signer="$2"
	    shift 2
	    ;;

	--capital)
	    capital="$2"
	    shift 2
	    ;;

	--date)
	    date="$2"
	    shift 2
	    ;;

	--address)
	    address="$2"
	    shift 2
	    ;;

	--)
	    shift
	    break
	    ;;

	*)
	    echo "$0: getopt error!" >&2
	    exit 1
	    ;;
    esac
done

if [ -z "$template_file" -o -z "$output" -o -z "$company" -o -z "$rcs" \
    -o -z "$business_entity" -o -z "$contract_signer_role" -o \
    -z "$contract_signer" -o -z "$capital" -o -z "$address" -o -z "$date" ]
then
    usage
    exit 1
fi

# Create temporary file
tempfile=$(tempfile -s '.pdf')
trap "rm -f $tempfile" EXIT HUP INT TRAP TERM

# LANG is required by pandoc
export LANG='en_US.UTF-8'

cd $(dirname $0)

sed \
    -e "s/_COMPANY_/$company/g" \
    -e "s/_BUSINESS_ENTITY_/$business_entity/g" \
    -e "s/_RCS_/$rcs/g" \
    -e "s/_CONTRACT_SIGNER_ROLE_/$contract_signer_role/g" \
    -e "s/_CONTRACT_SIGNER_/$contract_signer/g" \
    -e "s/_CAPITAL_/$capital/g" \
    -e "s/_ADDRESS_/$address/g" \
    -e "s|_DATE_|$date|g" \
    $template_file \
    | pandoc -V lang=french -V geometry:a4paper --template=contract.latex \
    -o $tempfile

if ls append-*.pdf >/dev/null 2>/dev/null
then
    pdftk $tempfile ${template_file%.md}-append-*.pdf cat output $output
else
    mv $tempfile $output
fi
