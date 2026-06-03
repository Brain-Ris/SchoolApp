<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Classe;
use App\Models\Matiere;
use App\Models\Eleve;
use App\Models\Paiement;
use App\Models\Note;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        /*
        |--------------------------------------------------------------------------
        | Gestionnaire
        |--------------------------------------------------------------------------
        */

        User::firstOrCreate(
            ['email' => 'admin@gmail.com'],
            [
                'name' => 'Administrateur',
                'password' => Hash::make('Admin@1234'),
                'role' => 'gestionnaire',
                'is_first_login' => true,
            ]
        );

        /*
        |--------------------------------------------------------------------------
        | Matières
        |--------------------------------------------------------------------------
        */

        $matieres = collect([
            'Mathématiques',
            'Français',
            'Histoire',
            'Géographie',
            'Sciences',
            'Éducation Civique',
            'Anglais',
            'Sport',
        ])->map(function ($nom) {
            return Matiere::create([
                'nom' => $nom,
            ]);
        });

        /*
        |--------------------------------------------------------------------------
        | Classes
        |--------------------------------------------------------------------------
        */

        $classesData = [
            ['CP1', 50000],
            ['CP2', 50000],
            ['CE1', 60000],
            ['CE2', 60000],
            ['CM1', 75000],
            ['CM2', 75000],
        ];

        /*
        |--------------------------------------------------------------------------
        | Enseignants
        |--------------------------------------------------------------------------
        */

        $enseignantsData = [
            ['Pierre KABORE', 'pierre@schoolapp.com'],
            ['Awa OUEDRAOGO', 'awa@schoolapp.com'],
            ['Moussa TRAORE', 'moussa@schoolapp.com'],
            ['Fatou ZONGO', 'fatou@schoolapp.com'],
            ['Issa SAWADOGO', 'issa@schoolapp.com'],
            ['Mariam BARRY', 'mariam@schoolapp.com'],
        ];

        foreach ($classesData as $index => $data) {

            [$nomClasse, $frais] = $data;

            $classe = Classe::create([
                'nom' => $nomClasse,
                'frais' => $frais,
            ]);

            /*
            |--------------------------------------------------------------------------
            | Enseignant
            |--------------------------------------------------------------------------
            */

            $enseignant = User::create([
                'name' => $enseignantsData[$index][0],
                'email' => $enseignantsData[$index][1],
                'password' => Hash::make('Password@123'),
                'role' => 'enseignant',
                'classe_id' => $classe->id,
                'is_first_login' => true,
            ]);

            /*
            |--------------------------------------------------------------------------
            | Matières de la classe
            |--------------------------------------------------------------------------
            */

            foreach ($matieres as $matiere) {

                $bareme = $matiere->nom === 'Sport'
                    ? 10
                    : 20;

                $classe->matieres()->attach(
                    $matiere->id,
                    [
                        'bareme' => $bareme,
                        'user_id' => $enseignant->id,
                    ]
                );
            }

            /*
            |--------------------------------------------------------------------------
            | Élèves
            |--------------------------------------------------------------------------
            */

            for ($i = 1; $i <= 20; $i++) {

                $genre = $i <= 10 ? 'M' : 'F';

                $eleve = Eleve::create([
                    'nom' => fake()->lastName(),
                    'prenom' => fake()->firstName(),
                    'genre' => $genre,
                    'classe_id' => $classe->id,
                    'photo' => null,
                ]);

                /*
                |--------------------------------------------------------------------------
                | Paiements réalistes
                |--------------------------------------------------------------------------
                */

                $fraisClasse = $classe->frais;
                $montantPaye = 0;

                $nbVersements = rand(1, 4);

                for ($j = 0; $j < $nbVersements; $j++) {

                    $reste = $fraisClasse - $montantPaye;

                    if ($reste <= 0) {
                        break;
                    }

                    $montant = rand(
                        1000,
                        min(20000, $reste)
                    );

                    $montantPaye += $montant;

                    Paiement::create([
                        'eleve_id' => $eleve->id,
                        'montant' => $montant,
                        'created_at' => now()->subDays(rand(1, 180)),
                        'updated_at' => now(),
                    ]);
                }

                /*
                |--------------------------------------------------------------------------
                | Niveau de l'élève
                |--------------------------------------------------------------------------
                */

                $niveau = rand(1, 100);

                if ($niveau <= 25) {

                    // Élève faible
                    $minPourcentage = 0.10;
                    $maxPourcentage = 0.50;

                } elseif ($niveau <= 75) {

                    // Élève moyen
                    $minPourcentage = 0.40;
                    $maxPourcentage = 0.80;

                } else {

                    // Excellent élève
                    $minPourcentage = 0.70;
                    $maxPourcentage = 1.00;
                }

                /*
                |--------------------------------------------------------------------------
                | Matières absentes selon le trimestre
                |--------------------------------------------------------------------------
                */

                $matieresAbsentes = [];

                for ($trimestre = 1; $trimestre <= 3; $trimestre++) {

                    $matieresAbsentes[$trimestre] =
                        $classe->matieres
                            ->shuffle()
                            ->take(rand(0, 2))
                            ->pluck('id')
                            ->toArray();
                }

                /*
                |--------------------------------------------------------------------------
                | Notes
                |--------------------------------------------------------------------------
                */

                foreach ($classe->matieres as $matiere) {

                    $bareme = $matiere->pivot->bareme;

                    for ($trimestre = 1; $trimestre <= 3; $trimestre++) {

                        if (
                            in_array(
                                $matiere->id,
                                $matieresAbsentes[$trimestre]
                            )
                        ) {
                            continue;
                        }

                        Note::create([
                            'eleve_id'   => $eleve->id,
                            'matiere_id' => $matiere->id,
                            'trimestre'  => $trimestre,

                            'valeurs' => fake()->randomFloat(
                                2,
                                round($bareme * $minPourcentage, 2),
                                round($bareme * $maxPourcentage, 2)
                            ),
                        ]);
                    }
                }
            }
        }
    }
}