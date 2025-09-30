<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\ORM\Query;
use App\Entity\MicroPost;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\Collection;

/**
 * @extends ServiceEntityRepository<MicroPost>
 */
class MicroPostRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MicroPost::class);
    }

    /**
     * @return MicroPost[] Returns an array of MicroPost objects
     */

    public function findAllWithAllData(): array
    {
        return $this->findAllQuery(
            withComments: true,
            withLikes: true,
            withAuthors: true,
            withProfiles: true
        )->getQuery()->getResult();
    }



    public function findAllWithComments(): array
    {
        return $this->findAllQuery(
            withComments: true
        )->getQuery()->getResult();
    }

    public function findAllByAuthor(
        int | User $author
    ): array {
        return $this->findAllQuery(
            withComments: true,
            withLikes: true,
            withAuthors: true,
            withProfiles: true
        )->where('p.author = :author')->setParameter(
            'author',
            $author instanceof User ? $author->getId() : $author
        )->getQuery()
            ->getResult();
    }

    public function findAllByTopLiked(): array
    {
        // Riusiamo la nostra query di base per avere già tutti i dati (autore, commenti, ecc.)
        return $this->findAllQuery(
            withComments: true,
            withLikes: true,
            withAuthors: true,
            withProfiles: true
        )
            // Aggiungiamo il conteggio dei like come campo calcolato
            ->addSelect('COUNT(l) as HIDDEN like_count')
            // Raggruppiamo per post, altrimenti il conteggio non funzionerebbe
            ->groupBy('p.id')
            // Ordiniamo per il nostro campo calcolato, in ordine decrescente
            ->orderBy('like_count', 'DESC')
            ->getQuery()
            ->getResult();
    }
    public function findAllByFollows(User $user): array
    {
        // Partiamo sempre dalla nostra query ottimizzata
        return $this->findAllQuery(
            withComments: true,
            withLikes: true,
            withAuthors: true,
            withProfiles: true
        )
            // Aggiungiamo la condizione fondamentale:
            // "dove l'autore del post (p.author) è NELLA lista dei seguiti (:follows)"
            ->where('p.author IN (:follows)')
            ->setParameter('follows', $user->getFollows())
            // L'ordinamento per data di creazione è già incluso in findAllQuery, perfetto!
            ->getQuery()
            ->getResult();
    }


    public function findAllByAuthors(
        Collection|array $authors
    ): array {
        return $this->findAllQuery(
            withComments: true,
            withLikes: true,
            withAuthors: true,
            withProfiles: true
        )
            ->where('p.author IN (:authors)')
            ->setParameter('authors', $authors)
            ->getQuery()
            ->getResult();
    }

    public function findAllWithMinLikes(int $minLikes): array
    {
        // 1. CREIAMO LA SOTTOQUERY PER TROVARE SOLO GLI ID DEI POST CHE CI INTERESSANO
        $subQueryBuilder = $this->createQueryBuilder('p_sub');
        $postIds = $subQueryBuilder
            ->select('p_sub.id')
            ->innerJoin('p_sub.likedBy', 'l_sub') // Usiamo INNER JOIN perché ci interessano solo i post con like
            ->groupBy('p_sub.id')
            ->having('COUNT(l_sub) >= :minLikes')
            ->setParameter('minLikes', $minLikes)
            ->getQuery()
            ->getSingleColumnResult(); // Otteniamo un array semplice di ID, es: [1, 5, 12]

        // Se nessun post soddisfa la condizione, la sottoquery restituirà un array vuoto.
        // In questo caso, per evitare errori, restituiamo subito un array vuoto.
        if (empty($postIds)) {
            return [];
        }

        // 2. CREIAMO LA QUERY PRINCIPALE USANDO GLI ID TROVATI
        // Ora recuperiamo gli oggetti MicroPost completi, usando la nostra query riutilizzabile
        return $this->findAllQuery(
            withComments: true,
            withLikes: true,
            withAuthors: true,
            withProfiles: true
        )
            ->where('p.id IN (:postIds)') // La condizione chiave: "dove l'ID del post è IN questa lista"
            ->setParameter('postIds', $postIds)
            ->getQuery()
            ->getResult();
    }



    private function findAllQuery(
        bool $withComments = false,
        bool $withLikes = false,
        bool $withAuthors = false,
        bool $withProfiles = false,

    ): QueryBuilder {

        // Sintassi del "Method Chaining" (a catena) 
        $query = $this->createQueryBuilder('p');

        if ($withComments) {
            $query->leftJoin('p.comments', 'c')
                ->addSelect('c');
        }
        if ($withLikes) {
            $query->leftJoin('p.likedBy', 'l')
                ->addSelect('l');
        }
        if ($withAuthors || $withProfiles) {
            $query->leftJoin('p.author', 'a')
                ->addSelect('a');
        }
        if ($withProfiles) {
            $query->leftJoin('a.userProfile', 'up')
                ->addSelect('up');
        }
        return $query->orderBy('p.created', 'DESC');
    }
}




    //    public function findOneBySomeField($value): ?MicroPost
    //    {
    //        return $this->createQueryBuilder('m')
    //            ->andWhere('m.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
