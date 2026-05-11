<?php

  # project: eBrigade
  # homepage: https://ebrigade.app
  # version: 5.3

  # Copyright (C) 2004, 2021 Nicolas MARCHE (eBrigade Technologies)
  # This program is free software; you can redistribute it and/or modify
  # it under the terms of the GNU General Public License as published by
  # the Free Software Foundation; either version 2 of the License, or
  # (at your option) any later version.
  #
  # This program is distributed in the hope that it will be useful,
  # but WITHOUT ANY WARRANTY; without even the implied warranty of
  # MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  # GNU General Public License for more details.
  # You should have received a copy of the GNU General Public License
  # along with this program; if not, write to the Free Software
  # Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA

include_once ("config.php");

$section=(isset($_GET['id'])?intval($_GET['id']):-1);
$sections=get_family("$section");
$site = $cisname.(($section>0)?" - ".get_section_name($section):"") ;
$url=get_plain_url($cisurl);
$siteurl = "http://".$url;
$siteinfo = $cisname;
$siterss = "http://".$url."/index.php?";

// Récupère le flux selon type_evenement, séparé par des virgules
if ( isset($_GET['f'])) $F="'".str_replace(",","','",secure_input($dbc,$_GET['f'],$strict=true))."'";
else $F='';
$flux=strtoupper($F);

$logo=get_logo();

$sql="select distinct
        e.E_CODE,
        te.TE_LIBELLE,
        concat('evenement=',e.E_CODE) rsslink, 
        date_format(e.E_CREATE_DATE,'%a, %e %b %Y %T GMT') rsspubdate,
        e.E_COMMENT2,
        e.TE_CODE,
        e.E_LIBELLE,
        e.E_LIEU rsslieu,
        e.S_ID,
        e.E_PARENT,
        e.PS_ID,
        e.TF_CODE,
        e.E_CLOSED,
        e.E_NB,
        e.E_NB_STAGIAIRES,
        s.S_CODE,
        s.S_DESCRIPTION,
        e.E_ADDRESS,
        c.C_ID,
        c.C_NAME
        from evenement_horaire eh, type_evenement te, section s, evenement e
        left join company c on c.c_id=e.c_id
         where e.S_ID in( $sections )
         and e.S_ID = s.S_ID
         and eh.E_CODE = e.E_CODE
        and e.TE_CODE = te.TE_CODE
        and ( TO_DAYS(NOW()) - TO_DAYS(eh.EH_DATE_DEBUT) <= 5 or eh.EH_DATE_DEBUT > NOW())
        and e.E_VISIBLE_OUTSIDE=1
        and e.E_CANCELED=0 ";
$sql.=    (($flux!="")?" and e.TE_CODE in($flux) ":""); // Choix d'un flux particulier
$sql.=    " order by eh.EH_DATE_DEBUT asc, eh.EH_DEBUT asc";

$p_head = "<?xml version=\"1.0\" encoding=\"iso-8859-1\" ?>
<?xml-stylesheet type=\"text/xsl\" href=\"rss.xsl\" ?>
<rss version=\"2.0\" xmlns:atom=\"http://www.w3.org/2005/Atom\">
<channel>
<title>$site</title>
<link>$siteurl</link>
<image>
<title>$site</title>
<link>$siteurl</link>
<url>$logo</url>
</image>
<description>$siteinfo</description>
<language>fr</language>
";
$p_foot = "</channel>
</rss>";
$p_item = "";

$show_organisateur=false;
$res = mysqli_query($dbc,$sql);
while($rows = mysqli_fetch_array($res)){
    $title = html_entity_decode(substr(strtoupper(fixcharset($rows['TE_LIBELLE']." - ".$rows['E_LIBELLE'])),0,70));
    $link = $rows['rsslink'];
    $permalink = $link;
    $pubdate = $rows['rsspubdate'];
    $evtlieu=html_entity_decode($rows['rsslieu']);
    $comment=$rows['E_COMMENT2'];
    $address=$rows['E_ADDRESS'];
    $cid=$rows['C_ID'];
    $stagiaires=intval($rows['E_NB_STAGIAIRES']);
    $company=$rows['C_NAME'];
    $organisateur=html_entity_decode($rows['S_CODE']." - ".$rows['S_DESCRIPTION']);
    $S_ID = $rows['S_ID'];    
    $datesheures=get_dates_heures($rows['E_CODE']);    
    $p_item .= "<item>
<title>".$title."</title>
<link>".$siterss.$link."</link>
<guid isPermaLink=\"false\">".$permalink."</guid>
<description>";
$p_item .="- organisé par: ".$organisateur;
$p_item .="\n- dates: ".$datesheures;
if ( $stagiaires <> "") $p_item .="\n- places stagiaires: ".$stagiaires;
$p_item .="\n- lieu: ".$evtlieu;
if ( $address <> "") $p_item .= "\n- Adresse exacte: ".$address;
if ( $cid > 0 and $company <> "") $p_item .= "\n- Pour le compte de: ".$company;
$p_item .= "\n".$comment;
$p_item .="</description>
<pubDate>".$pubdate."</pubDate>
</item>
";
}
$p_head=strtr($p_head,'&','-');
$p_item=strtr($p_item,'&','-');
echo $p_head;
echo $p_item;
echo $p_foot;

?>
