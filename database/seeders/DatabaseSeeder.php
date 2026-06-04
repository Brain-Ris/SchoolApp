<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Classe;
use App\Models\Matiere;
use App\Models\Eleve;
use App\Models\Note;
use App\Models\Paiement;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('Début du Seeding SchoolApp avec nouvelles matières...');

        // ====================== 1. CRÉATION DES MATIÈRES ======================
        $this->command->info('Création des matières...');

        $matieresData = [
            ['nom' => 'Opérations'],
            ['nom' => 'Problèmes'],
            ['nom' => 'Expression écrite'],
            ['nom' => 'Etude de texte'],
            ['nom' => 'Histoire-Géographie'],
            ['nom' => 'Lecture'],
            ['nom' => 'Récitation-Chant'],
            ['nom' => 'Ecriture'],
            ['nom' => 'Exercices-Observations'],
            ['nom' => 'Sport'],
            ['nom' => 'Anglais'],
            ['nom' => 'Dessin'],
            ['nom' => 'Education Civique'],
        ];

        $matieres = [];
        foreach ($matieresData as $data) {
            $matieres[$data['nom']] = Matiere::create($data);
        }

        // ====================== 2. CRÉATION DU GESTIONNAIRE ======================
        $this->command->info('Création du compte Gestionnaire...');

        User::create([
            'name'            => 'Administrateur',
            'email'           => 'admin@gmail.com',
            'password'        => Hash::make('Admin@1234'),
            'role'            => 'gestionnaire',
            'is_first_login'  => true,
        ]);

        // ====================== 3. CRÉATION DES CLASSES, ENSEIGNANTS & ÉLÈVES ======================
        $this->command->info('Création des classes, enseignants et élèves...');

        $classesData = [
            ['nom' => 'CP1', 'frais' => 75000],
            ['nom' => 'CP2', 'frais' => 75000],
            ['nom' => 'CE1', 'frais' => 85000],
            ['nom' => 'CE2', 'frais' => 85000],
            ['nom' => 'CM1', 'frais' => 95000],
            ['nom' => 'CM2', 'frais' => 95000],
        ];

        $enseignants = [
            ['name' => 'Pierre KABORE', 'email' => 'pierre@schoolapp.com'],
            ['name' => 'Awa OUEDRAOGO', 'email' => 'awa@schoolapp.com'],
            ['name' => 'Moussa TRAORE', 'email' => 'moussa@schoolapp.com'],
            ['name' => 'Fatou ZONGO', 'email' => 'fatou@schoolapp.com'],
            ['name' => 'Issa SAWADOGO', 'email' => 'issa@schoolapp.com'],
            ['name' => 'Mariam BARRY', 'email' => 'mariam@schoolapp.com'],
        ];

        foreach ($classesData as $index => $data) {
            $classeNom = $data['nom'];

            // Création de l'enseignant
            $enseignant = User::create([
                'name'           => $enseignants[$index]['name'],
                'email'          => $enseignants[$index]['email'],
                'password'       => Hash::make('Password@123'),
                'role'           => 'enseignant',
                'is_first_login' => true,
            ]);

            // Création de la classe
            $classe = Classe::create([
                'nom'  => $classeNom,
                'frais' => $data['frais'],
            ]);

            // Association enseignant → classe
            $enseignant->update(['classe_id' => $classe->id]);

            // ====================== ASSOCIATION DES MATIÈRES PAR CLASSE ======================
            $this->associerMatieresAClasse($classe, $matieres, $classeNom, $enseignant->id);

            // Création des élèves + notes (trimestre 1 & 2 seulement)
            $this->creerElevesAvecNotes($classe);
        }

        $this->command->info('Seeding terminé avec succès ! 🎉');
    }

    /**
     * Associe les bonnes matières selon le niveau de la classe
     */
    private function associerMatieresAClasse(Classe $classe, $matieres, string $classeNom, int $enseignantId)
    {
        $isCP = in_array($classeNom, ['CP1', 'CP2']);
        $isCE_CM = ! $isCP; // CE1, CE2, CM1, CM2

        // Matières communes à toutes les classes
        $classe->matieres()->attach($matieres['Opérations']->id, ['bareme' => 20, 'user_id' => $enseignantId]);
        $classe->matieres()->attach($matieres['Expression écrite']->id, ['bareme' => 20, 'user_id' => $enseignantId]);
        $classe->matieres()->attach($matieres['Lecture']->id, ['bareme' => 20, 'user_id' => $enseignantId]);
        $classe->matieres()->attach($matieres['Récitation-Chant']->id, ['bareme' => 10, 'user_id' => $enseignantId]);
        $classe->matieres()->attach($matieres['Exercices-Observations']->id, ['bareme' => 10, 'user_id' => $enseignantId]);
        $classe->matieres()->attach($matieres['Sport']->id, ['bareme' => 10, 'user_id' => $enseignantId]);
        $classe->matieres()->attach($matieres['Dessin']->id, ['bareme' => 10, 'user_id' => $enseignantId]);
        $classe->matieres()->attach($matieres['Education Civique']->id, ['bareme' => 10, 'user_id' => $enseignantId]);

        // Matières spécifiques
        if ($isCP) {
            $classe->matieres()->attach($matieres['Ecriture']->id, ['bareme' => 10, 'user_id' => $enseignantId]);
        }

        if ($isCE_CM) {
            $classe->matieres()->attach($matieres['Problèmes']->id, ['bareme' => 20, 'user_id' => $enseignantId]);
            $classe->matieres()->attach($matieres['Histoire-Géographie']->id, ['bareme' => 20, 'user_id' => $enseignantId]);
            $classe->matieres()->attach($matieres['Anglais']->id, ['bareme' => 20, 'user_id' => $enseignantId]);
        }

        if (in_array($classeNom, ['CM1', 'CM2'])) {
            $classe->matieres()->attach($matieres['Etude de texte']->id, ['bareme' => 20, 'user_id' => $enseignantId]);
        }
    }

    /**
     * Crée les élèves et leurs notes (Trimestre 1 et 2 seulement)
     */
    private function creerElevesAvecNotes(Classe $classe)
    {
        $prenomsGarcons = ['Abdoul', 'Ibrahim', 'Moussa', 'Amadou', 'Souleymane', 'Yacouba', 'Boubacar', 'Hamza'];
        $prenomsFilles  = ['Fatima', 'Aicha', 'Mariam', 'Kadiatou', 'Rachida', 'Aminata', 'Salimata', 'Hafsatou'];
        $noms = ['SOME', 'OUEDRAOGO', 'TRAORE', 'KABORE', 'SAWADOGO', 'ZONGO', 'DIALLO', 'BARRY'];

        $elevesCount = rand(8, 11);

        for ($i = 0; $i < $elevesCount; $i++) {
            $genre   = $i % 2 === 0 ? 'M' : 'F';
            $prenom  = $genre === 'M' 
                        ? $prenomsGarcons[array_rand($prenomsGarcons)] 
                        : $prenomsFilles[array_rand($prenomsFilles)];

            $nom = $noms[array_rand($noms)];

            $eleve = Eleve::create([
                'classe_id' => $classe->id,
                'nom'       => $nom,
                'prenom'    => $prenom,
                'genre'     => $genre,
                'photo'     => null,
            ]);

            // Récupérer les matières de la classe avec barème
            $matieresClasse = $classe->matieres()->withPivot('bareme')->get();

            foreach ($matieresClasse as $matiere) {
                $bareme = $matiere->pivot->bareme;

                // Notes obligatoires pour Trimestre 1 et 2
                for ($trimestre = 1; $trimestre <= 2; $trimestre++) {
                    $note = rand(45, $bareme * 10) / 10; // Notes plus réalistes (min 4.5)

                    Note::create([
                        'eleve_id'    => $eleve->id,
                        'matiere_id'  => $matiere->id,
                        'trimestre'   => $trimestre,
                        'valeurs'     => $note,
                    ]);
                }
                // Trimestre 3 reste vide
            }

            // Paiements
            if ($i % 3 !== 0) {
                Paiement::create([
                    'eleve_id' => $eleve->id,
                    'montant'  => rand(25000, 50000),
                ]);
            }
        }
    }
}