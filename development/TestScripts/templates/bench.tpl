<h1>Dwoo VS Smarty3b8</h1>
<h2>Vars</h2>
INT : {$int}<br/>
STR : {$string}<br/>
DATE : {$date|date_format:"%d-%m-%Y"}<br/>
TABLEAU :
{foreach from=$tableau  item=row}
    {$row}
    <br/>
{/foreach}
<br/>
<br/>
TABLEAU deux dimentions<br/>
{foreach from=$tableau2d  item=row}
    {foreach from=$row item=row2}
        {$row2}
        <br/>
    {/foreach}
{/foreach}
<br/>
<br/>
Objet<br/>
nom:{$objet->nom}<br/>
prenom:{$objet->prenom}<br/>
telephone:{$objet->telephone}<br/>
adresse:{$objet->adresse->adresse}<br/>
code postal:{$objet->adresse->codepostal}<br/>
ville:{$objet->adresse->ville}<br/>

{include file='extrainclude.tpl'}
{include file='report.tpl'}