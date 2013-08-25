{section start=0 loop=30 name=objectloop}
    Objet {$smarty.section.objectloop.index}
    <br/>
    nom:{$objet->nom}
    <br/>
    prenom:{$objet->prenom}
    <br/>
    telephone:{$objet->telephone}
    <br/>
    adresse:{$objet->adresse->adresse}
    <br/>
    code postal:{$objet->adresse->codepostal}
    <br/>
    ville:{$objet->adresse->ville}
    <br/>
{/section}