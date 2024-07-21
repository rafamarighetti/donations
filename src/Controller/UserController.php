<?php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Entity\User;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UserController extends AbstractController
{
    public function users()
    {
        $users = $this->getDoctrine()->getRepository(User::class)->findAll();

        foreach($users as $key => $user){
            $users[$key] =  [
                    'id'=>$user->getId(),
                    'userEmail'=>$user->getEmail(),
                    'firstName' => $user->getFirstName(),
                    'lastName' => $user->getLastName(),
                    'userRole' => $user->getRoles()
            ];
           
        }

        return new JsonResponse($users, 200);
    }

    public function create(Request $request, UserPasswordEncoderInterface $passwordEncoder)
    {
        $request = json_decode($request->getContent(), true);
        
        $data = [
            'firstName' => $request['firstName'],
            'lastName' => $request['lastName'],
            'userPassword' => $request['userPassword'],
            'userEmail' => $request['userEmail']
        ];

        $validator = Validation::createValidator();
        $constraint = new Assert\Collection(array(
            // the keys correspond to the keys in the input array
            'firstName' => new Assert\Length(array('min' => 1)),
            'lastName' => new Assert\Length(array('min' => 1)),
            'userPassword' => new Assert\Length(array('min' => 1)),
            'userEmail' => new Assert\Email()
        ));
        $violations = $validator->validate($data, $constraint);
        if ($violations->count() > 0) {
            return new JsonResponse(["error" => (string)$violations], 500);
        }
        $firstName = $data['firstName'];
        $lastName = $data['lastName'];
        $userPassword = $data['userPassword'];
        $userEmail = $data['userEmail'];
        $userRole = $request['userRole'];

        $user = new User();
        $user
            ->setFirstName($firstName)
            ->setLastName($lastName)
            ->setPassword($userPassword)
            ->setEmail($userEmail)
            ->setRoles($userRole)
            ->onPrePersist()
        ;

        $password = $passwordEncoder->encodePassword($user, $user->getPassword());
        $user->setPassword($password);

        try {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($user);
            $entityManager->flush();
        } catch (\Exception $e) {
            return new JsonResponse(["error" => $e->getMessage()], 500);
        }
        return new JsonResponse(["success" => $user->getUsername(). " has been registered!"], 200);
    }

    public function delete(int $id)
    {
        $repository = $this->getDoctrine()->getRepository(User::class);
        $user = $repository->findOneBy([
            'id' => $id
        ]);
        

        if (!$user) {
            return new JsonResponse(["error" => 'User not exists'], 500);
        } else {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($user);
            $entityManager->flush();
            return new Response(sprintf('%s successfully removed.', $user->getLastName()));
        }
    }
    
    public function edit(int $id, Request $request, UserPasswordEncoderInterface $passwordEncoder)
    {
        $request = json_decode($request->getContent(), true);
        try {
            $data = [];
            $validateData = [];

            if ($id) {
                $repository = $this->getDoctrine()->getRepository(User::class);
                $user = $repository->findOneBy([
                    'id' => $id,
                ]);

                if (!$id) {
                    return new JsonResponse(["error" => 'User not exists'], 500);
                }
            } else {
                return new JsonResponse(["error" => 'Please set param project ID in route'], 500);
            }

            if ($request['firstName']) {
                $data['firstName'] = $request['firstName'];
                $validateData['firstName'] = new Assert\Length(array('min' => 1));
                $user->setFirstName($data['firstName']);
            }

            if ($request['lastName']) {
                $data['lastName'] = $request['lastName'];
                $validateData['lastName'] = new Assert\Length(array('min' => 1));
                $user->setLastName($data['lastName']);
            }

            if ($request['userEmail']) {
                $data['userEmail'] = $request['userEmail'];
                $validateData['userEmail'] = new Assert\Length(array('min' => 1));
                $user->setEmail($data['userEmail']);
            }

            if ($request['userPassword']) {
                $data['userPassword'] = $request['userPassword'];
                $validateData['userPassword'] = new Assert\Length(array('min' => 1));
                $user->setPassword($data['userPassword']);

                $password = $passwordEncoder->encodePassword($user, $user->getPassword());
                $user->setPassword($password);
            }

            if (!empty($validateData)) {
                $validator  = Validation::createValidator();
                $constraint = new Assert\Collection($validateData);

                $violations = $validator->validate($data, $constraint);
                if ($violations->count() > 0) {
                    return new JsonResponse(["error" => (string)$violations], 500);
                }
            }

            $userRole = $request['userRole'];

            if ($userRole) {
                $user->setRoles($userRole);
            }

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->flush();
        } catch (\Exception $e) {
            return new JsonResponse(["error" => $e->getMessage()], 500);
        }
        return new Response(sprintf('%s already upadeted!', $user->getFirstName()));
    }

    public function view(string $id)
    {
        $repository = $this->getDoctrine()->getRepository(User::class);
        $user = $repository->findOneBy([
            'id' => $id,
        ]);

        if (!$user) {
            return new JsonResponse(["error" => 'User not exists'], 500);
        }

        $data = [
            'id'=>$user->getId(),
            'userEmail'=>$user->getEmail(),
            'firstName' => $user->getFirstName(),
            'lastName' => $user->getLastName(),
            'userRole' => $user->getRoles()
        ];

        return new JsonResponse($data, 200);
    }
}