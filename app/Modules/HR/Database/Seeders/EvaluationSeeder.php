<?php

namespace App\Modules\HR\Database\Seeders;

use App\Modules\HR\Models\EvaluationTemplate;
use App\Modules\HR\Models\EvaluationCriteria;
use Illuminate\Database\Seeder;

class EvaluationSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedPerformanceTemplate();
        $this->seedProbationTemplate();
    }

    private function seedPerformanceTemplate(): void
    {
        $template = EvaluationTemplate::updateOrCreate(
            ['name' => 'Evaluation de Performance Annuelle'],
            [
                'description' => 'Template standard pour les evaluations de performance annuelles',
                'type' => 'performance',
                'is_active' => true,
                'include_self_evaluation' => true,
                'include_manager_evaluation' => true,
                'include_peer_evaluation' => false,
            ]
        );

        $criteria = [
            // Competences techniques
            [
                'category' => 'technical',
                'name' => 'Maitrise technique',
                'description' => 'Niveau de competence dans les domaines techniques du poste',
                'weight' => 3,
                'max_score' => 5,
                'order' => 1,
            ],
            [
                'category' => 'technical',
                'name' => 'Qualite du travail',
                'description' => 'Precision, rigueur et fiabilite du travail produit',
                'weight' => 3,
                'max_score' => 5,
                'order' => 2,
            ],
            [
                'category' => 'technical',
                'name' => 'Productivite',
                'description' => 'Capacite a atteindre les objectifs dans les delais',
                'weight' => 2,
                'max_score' => 5,
                'order' => 3,
            ],
            // Competences comportementales
            [
                'category' => 'behavioral',
                'name' => 'Travail en equipe',
                'description' => 'Capacite a collaborer efficacement avec les collegues',
                'weight' => 2,
                'max_score' => 5,
                'order' => 4,
            ],
            [
                'category' => 'behavioral',
                'name' => 'Communication',
                'description' => 'Clarte et efficacite dans la communication orale et ecrite',
                'weight' => 2,
                'max_score' => 5,
                'order' => 5,
            ],
            [
                'category' => 'behavioral',
                'name' => 'Initiative et proactivite',
                'description' => 'Capacite a prendre des initiatives et anticiper les besoins',
                'weight' => 2,
                'max_score' => 5,
                'order' => 6,
            ],
            [
                'category' => 'behavioral',
                'name' => 'Adaptabilite',
                'description' => 'Capacite a s\'adapter aux changements et nouvelles situations',
                'weight' => 1,
                'max_score' => 5,
                'order' => 7,
            ],
            // Objectifs
            [
                'category' => 'goals',
                'name' => 'Atteinte des objectifs',
                'description' => 'Degre de realisation des objectifs fixes',
                'weight' => 3,
                'max_score' => 5,
                'order' => 8,
            ],
            // Leadership (si applicable)
            [
                'category' => 'leadership',
                'name' => 'Leadership',
                'description' => 'Capacite a guider et motiver une equipe (si applicable)',
                'weight' => 2,
                'max_score' => 5,
                'order' => 9,
                'is_required' => false,
            ],
        ];

        foreach ($criteria as $criterion) {
            EvaluationCriteria::updateOrCreate(
                [
                    'evaluation_template_id' => $template->id,
                    'name' => $criterion['name'],
                ],
                array_merge($criterion, ['evaluation_template_id' => $template->id])
            );
        }
    }

    private function seedProbationTemplate(): void
    {
        $template = EvaluationTemplate::updateOrCreate(
            ['name' => 'Evaluation de Fin de Periode d\'Essai'],
            [
                'description' => 'Template pour evaluer les employes en fin de periode d\'essai',
                'type' => 'probation',
                'is_active' => true,
                'include_self_evaluation' => false,
                'include_manager_evaluation' => true,
                'include_peer_evaluation' => false,
            ]
        );

        $criteria = [
            [
                'category' => 'competencies',
                'name' => 'Adequation au poste',
                'description' => 'Le collaborateur possede-t-il les competences requises pour le poste?',
                'weight' => 3,
                'max_score' => 5,
                'order' => 1,
            ],
            [
                'category' => 'competencies',
                'name' => 'Capacite d\'apprentissage',
                'description' => 'Vitesse et qualite d\'acquisition des nouvelles connaissances',
                'weight' => 2,
                'max_score' => 5,
                'order' => 2,
            ],
            [
                'category' => 'behavioral',
                'name' => 'Integration dans l\'equipe',
                'description' => 'Qualite de l\'integration avec les collegues',
                'weight' => 2,
                'max_score' => 5,
                'order' => 3,
            ],
            [
                'category' => 'behavioral',
                'name' => 'Respect des regles',
                'description' => 'Respect du reglement interieur, ponctualite, assiduite',
                'weight' => 2,
                'max_score' => 5,
                'order' => 4,
            ],
            [
                'category' => 'behavioral',
                'name' => 'Motivation et engagement',
                'description' => 'Niveau d\'implication et de motivation',
                'weight' => 2,
                'max_score' => 5,
                'order' => 5,
            ],
            [
                'category' => 'technical',
                'name' => 'Autonomie',
                'description' => 'Capacite a travailler de maniere autonome',
                'weight' => 2,
                'max_score' => 5,
                'order' => 6,
            ],
        ];

        foreach ($criteria as $criterion) {
            EvaluationCriteria::updateOrCreate(
                [
                    'evaluation_template_id' => $template->id,
                    'name' => $criterion['name'],
                ],
                array_merge($criterion, ['evaluation_template_id' => $template->id])
            );
        }
    }
}
