{include file="header.tpl"}

{if isset($error)}
<font color="red">{$error}</font>
<br/>
<br/>
{/if}

<h1> {$company_info.name} </h1>

<h2> Invoices </h2>
<table border="1">
  <tr>
    <th> {t}Type{/t} </th>
    <th> {t}Reference{/t} </th>
    <th> {t}Date{/t}</th>
    <th> {t}Amount{/t} </th>
    <th> {t}Actions{/t} </th>
  </tr>

  {foreach from=$invoices key=k item=invoice}
  <tr>
    <td> {$invoice.type} </td>
    <td> {$invoice.invoice_reference} </td>
    <td> {$invoice.date|date_format:"%x"} </td>
    <td> {$invoice.amount|string_format:"%.2f"}&nbsp;&euro;</td>
    <td>
      {if $invoice.paid == true}
      paid.ico
      {else}
      unpaid.ico
      {/if}
      <a href="download?invoice_id={$invoice.id}">PDF</a>
    </td>
  </tr>

  {/foreach}

</table>

<!-- Link to the other companies, if any -->
{if count($companies) > 1}
  <form>
    <select name="company_id">

      {foreach from=$companies key=k item=company}
      <option value="{$company.id}"
        {if $company.id == $this_company_id}
          selected
        {/if}
      > {$company.name}
      </option>
      {/foreach}

    </select>
    <input type="submit" name="Change company" value="Change company">
 </form>
{/if}

{include file="footer.tpl"}
