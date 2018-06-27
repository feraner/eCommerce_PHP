<?php
return array(
	/*************** global ***************/
	'home'			=>	'Page d’accueil',
	'my_account'	=>	'Mon compte',
	'contactUs'		=>	'Contactez nous',
	'footer_0'		=>	'Vous recevrez ce message courrier électronique parce que vous êtes le membre inscrit du site %domain%.',
	'footer_1'		=>	"Pour savoir plus des informations, veuillez login in votre compte: %domain% et soumettre votre demande, ou bien communiquer simultanément.",
	'dear'			=>	'Cher %name%',
	'dearTo'		=>	'Cher client',
	'not_reply'		=>	"C’est le message courrier électronique avec la réponse automatique de %domain%. Vous ne besoin pas d’y répondre.",
	'copy_paste'	=>	"Veuillez cliquer le Web Link ci-dessous, ou copie ce Web Link à la colonne de l’explorateur pour faire les achats",
	'sincerely'		=>	'salutation',
	'customer'		=>	'Groupe service client %domain%',
	'queries'		=>	"Si vous avez les problèmes, n’hésitez pas à envoyer le message courrier électronique à notre groupe service client.",
	/*************** 系统邮件模板默认标题 ***************/
	'create_account'=>	'Bienvenue à {Domain}.',
	'forgot_password'=>	'Récupération de mot de passe {Domain}.',
	'validate_mail'	=>	'Cher {Email}, S\'il vous plaît vérifier votre adresse e-mail.',
	'order_create'	=>	'Passez une commande: {OrderNum}.',
	'order_payment'	=>	'Nous avons reçu de votre paiement pour la commande#{OrderNum}.',
	'order_shipped'	=>	'Votre commande#{OrderNum} a été expédiée.',
	'order_change'	=>	'Votre commande#{OrderNum} a changé pour {OrderStatus}.',
	'order_cancel'	=>	'Annuler une commande: {OrderNum}',
	/*************** create account ***************/
	'thanks'		=>	"Merci pour votre choix %domain%",
	'createTitle'	=>	"Vous êtes toujours un membre de notre entreprise, bienvenue à notre rang pour bénéficer une expérience de convenance et sécurité. Votre information du compte est ci-dessous:",
	'yUsername'		=>	"Votre identifiant",
	'yEmail'		=>	'Votre message courrier électronique',
	'yPassword'		=>	'Votre code secret',
	/*************** forgot password ***************/
	'not_reply_pwd'	=>	"C’est le message courrier électronique envoyé par %domain% suivant votre demande de recréation d’un code secret. Ne répondez pas ce message courrier électronique, s’il vous plaît. ",
	'steps'			=>	'Il faut récréer votre code secret et visiter votre compte %domain%, veuillez suivre les étapes ci-dessous',
	'pwdInfo_0'		=>	"Cliquez le Web Link ci-dessous, ou bien le copiez-collé à la colonne de l’adresse de votre l’explorateur.",
	'pwdInfo_1'		=>	'Web Link ci-dessus vous guidera à la page “récréation de code”.Remplissez les caractères conformes et cliquez “soumettre”，ainsi vous pouvez visiter votre compte immédiatement.',
	/*************** order cancel ***************/
	'cancelInfo'	=>	"Votre commande ＃%oid% <strong style='color:#FF0000;'>a déjà supprimé</strong>, si vous avez les problèmes, n’hésitez pas à
me contacter.",
	/*************** order change ***************/
	'changeInfo'	=>	'Votre commande ＃%oid% <strong style="color:#FF0000;">a déjà changé</strong>!',
	/*************** order create ***************/
	'createInfo'	=>	'Merci pour votre numéro de commande %oid% et domain%',
	'pleaseNote'	=>	"Attention, s’il vous plaît",
	'ourWebsite'	=>	'notre site Web',
	'createInfo_1'	=>	'Votre état de commande est %status%, ça signifie que vous n’accomplissez pas votre paiement. Nous ne pouvons garder votre commande que pour 7 jours.',
	'createInfo_2'	=>	"Vous devez payer <strong>%total_price%</strong>, veuillez soumettre votre numéro de commande, référence de commande, montant, nom et prénom d’expéditeur, pays.Après avoir reçu votre article Veuillez payer %domain% reste et informer votre méthode de <strong>%PaymentMethod%</strong>, merci!",
	'createInfo_2_0'=>	"Vous devez payer <strong>%total_price%</strong>",
	'createInfo_3'	=>	"Si vous voulez payer en ligne, veuillez cliquer ici pour continuer votre paiement >>",
	/*************** order payment ***************/
	'paymentInfo'	=>	'NNous avons reçu votre transfert de l’argent de commande ＃%oid%，merci!',
	/*************** order shipped ***************/
	'shippedInfo'	=>	'Votre commande ＃%oid% <strong style="color:#FF0000;">a déjà livrée</strong>!',
	'query'			=>	'Cliquez ici pour suivre votre commande',
	/*************** order detail ***************/
	'checkDetails'	=>	'Cliquez ici pour vérifier les informations en détail de votre commande',
	'details'		=>	'Vérifier les informations détaillées de votre commande',
	'orderNumber'	=>	'Numéro de commande',
	'orderDate'		=>	'Date de commande',
	'orderStatus'	=>	'État de commande',
	'paymentMethod'	=>	'Moyen de paiement',
	'shippingMethod'=>	"Moyen d’expédition",
	'trackingNumber'=>	'Numéro à suivre',
	'remarks'		=>	'NB',
	'itemCosts'		=>	'item',
	'discount'		=>	'réduction',
	'save'			=>	'économiser',
	'shipInsurance'	=>	'Frais de transport et l’assurance',
	'fee'			=>	'Frais de procédure',
	'coupon'		=>	'Coupon de réduction',
	'grandTotal'	=>	'Au total',
	'yShipAddress'	=>	'Votre adresse de livraison',
	'yBillAddress'	=>	'Votre adresse de billet',
	'phone'			=>	'Numéro de téléphone',
	'special'		=>	'Explication ou commentaire spécial',
	'orderItems'	=>	'Item de commande',
	'pictures'		=>	'image',
	'product'		=>	'produit',
	'price'			=>	'prix',
	'qty'			=>	'nombre',
	'total'			=>	'Total partiel',
	'shipsFrom'		=>	'Lieu d’expédition',
	/*************** validate mail ***************/
	'validateInfo_1'=>	'En considérant la cause de sécurité, veuillez <a href="%url%" target="_blank">cliquer ici</a> pour vérifier votre adresse
message courrier électronique.',
	'validateDetail'=>	"Si le Web Link ci-dessus n’affiche pas, veuillez copier-collé le site ci-dessous à l’éxplorateur pour le rechercher.",
	'validateInfo_2'=>	'Si vous avez les problèmes, n’hésitez pas à <a href="%url%" target="_blank">nous contacter</a>.'
);