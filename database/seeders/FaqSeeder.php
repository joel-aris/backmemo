<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Faq;
use Illuminate\Database\Seeder;

final class FaqSeeder extends Seeder
{
    public function run(): void
    {
        $faqs = [
            [
                'question' => 'Comment vérifier un pharmacien ?',
                'answer' => "Utilisez le formulaire de recherche sur la page d'accueil en saisissant le nom, le numéro d'ordre ou la ville du pharmacien. Vous pouvez également scanner le QR code présent sur sa carte professionnelle.",
                'category' => 'Vérification',
                'order' => 1,
            ],
            [
                'question' => "Comment s'inscrire sur la plateforme ?",
                'answer' => "Cliquez sur le bouton 'S'inscrire' en haut à droite, remplissez le formulaire avec votre nom complet et votre adresse email. Vous devrez ensuite vérifier votre email et activer la double authentification (2FA) pour accéder à toutes les fonctionnalités.",
                'category' => 'Inscription',
                'order' => 2,
            ],
            [
                'question' => 'Comment devenir pharmacien membre de l\'Ordre ?',
                'answer' => "Pour devenir pharmacien membre de l'Ordre des pharmaciens congolais, vous devez déposer une candidature depuis la page dédiée. Vous devrez fournir votre CV, une lettre de motivation et les pièces justificatives de votre diplôme.",
                'category' => 'Inscription',
                'order' => 3,
            ],
            [
                'question' => 'Qu\'est-ce que la carte professionnelle ?',
                'answer' => "La carte professionnelle est un document numérique sécurisé délivré à chaque pharmacien membre de l'Ordre. Elle contient un QR code permettant de vérifier instantanément l'authenticité du diplôme et le statut de la licence.",
                'category' => 'Vérification',
                'order' => 4,
            ],
            [
                'question' => 'Comment scanner un QR code ?',
                'answer' => "Depuis la page 'Scanner', utilisez la caméra de votre appareil pour scanner le QR code présent sur la carte professionnelle du pharmacien. La plateforme vous redirigera automatiquement vers sa fiche de vérification.",
                'category' => 'Utilisation',
                'order' => 5,
            ],
            [
                'question' => 'Que faire en cas de perte de carte ?',
                'answer' => "En cas de perte ou de vol de votre carte professionnelle, contactez immédiatement le secrétariat de l'Ordre des pharmaciens congolais pour demander sa réémission. Une procédure de déclaration de perte vous sera communiquée.",
                'category' => 'Gestion',
                'order' => 6,
            ],
            [
                'question' => 'Comment signaler un pharmacien non autorisé ?',
                'answer' => "Si vous suspectez qu'un pharmacien exerce sans être inscrit à l'Ordre, utilisez le formulaire de contact en précisant 'Signalement' dans l'objet. Toutes les informations transmises sont traitées confidentiellement.",
                'category' => 'Signalement',
                'order' => 7,
            ],
            [
                'question' => 'Quels sont les délais de traitement des candidatures ?',
                'answer' => "Les dossiers de candidature sont examinés par la commission d'admission dans un délai de 15 à 30 jours ouvrables. Vous recevrez une notification par email à chaque étape du processus.",
                'category' => 'Candidature',
                'order' => 8,
            ],
        ];

        foreach ($faqs as $faq) {
            Faq::query()->create($faq);
        }
    }
}
