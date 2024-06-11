<?php



function test(object $verifyEmailHelper, object $request, object $user, object $entityManagerInterface)
{
    $return = $verifyEmailHelper->validateEmailConfirmationFromRequest($request, (string) $user->getId(), $user->getEmail());


    $signature = $request->get('signature');

    $check = $entityManagerInterface->getRepository(EmailConfirmationRequest::class)->checkSignature($user, $signature);

    // $entityManagerInterface->remove($check);
    // $entityManagerInterface->flush();

    return $check;
}
dd(test($this->verifyEmailHelper, $request, $user, $this->entityManagerInterface));


