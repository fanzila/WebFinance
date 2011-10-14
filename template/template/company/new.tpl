{include file="header.tpl"}
<h1> Create company </h1>

{if isset($error)}
<font color="red">{$error}</font>
<br/>
<br/>
{/if}

<form method="POST">
  Company name:
  <input type="text" name="name"
	 value="{if isset($param.name) } $param.name {/if}" /> <br/>

  Address:
	  <input type="text" name="address1"
	  value="{if isset($param.address1) } $param.address1 {/if}" /> <br/>

	  <input type="text" name="address2"
	  value="{if isset($param.address2) } $param.address2 {/if}" /> <br/>

	  <input type="text" name="address3"
	  value="{if isset($param.address3) } $param.address3 {/if}" /> <br/>

  Zip code:
	  <input type="text" name="zip_code"
	  value="{if isset($param.zip_code) } $param.zip_code {/if}" /> <br/>

  City:
	  <input type="text" name="city"
	  value="{if isset($param.city) } $param.city {/if}" /> <br/>

  Country:
	<select name="country">
	  {foreach from=$countries key=code item=country}
		<option value="{$code}"
			{if $param.country == $code}
		  selected
		  {/if}
		  >{$country}</option>
		  {/foreach}
	</select> <br/>

	<input type="submit" name="Create company" value="Create company">

</form>
{include file="footer.tpl"}
