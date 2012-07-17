<?php

namespace BSP\AdyenBundle\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\Output;
use Symfony\Component\Console\Command\Command;

/**
 * Charges an specified amount to an RECURRING account on Adyen
 *
 * @author Alfons
 */
class ChargeCommand extends Command
{
    /**
     * @var \Symfony\Component\DependencyInjection\ContainerInterface
     */
    protected $container;

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->container = $this->getApplication()->getKernel()->getContainer();
    }

    protected function configure()
    {
        $this->setName('adyen:charge');
        $this->setDescription('Charges an account');
        $this->setDefinition(array(
            new InputArgument(
                'account',
                InputArgument::REQUIRED,
                'The ID of the account you want to charge'
            ),
            new InputArgument(
                    'email',
                    InputArgument::REQUIRED,
                    'The email of the shopper you want to charge'
            ),
            new InputArgument(
                    'amount',
                    InputArgument::REQUIRED,
                    'The amount you want to charge (in the lowest value)'
            ),
            new InputArgument(
                    'currency',
                    InputArgument::OPTIONAL,
                    'The currency you want to charge with'
            )
        ));
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $account  = $input->getArgument('account');
        $email    = $input->getArgument('email');
        $amount   = $input->getArgument('amount');
        $currency = $input->getArgument('currency');

        $adyen    = $this->container->get('adyen.service');

        $output->writeln("Charging <comment>". $amount . ( $currency? " ".$currency : "" ) . "</comment> to <comment>" . $email . "</comment> account ...");
        $charge = $adyen->charge( $account, $email, $amount, $currency );

        if($charge === false)
            $output->writeln( '<error>'.$adyen->getError().'</error>' );
        else
            $output->writeln( '<info>OK</info>' );
    }

    /**
     * @see Command
     */
    protected function interact(InputInterface $input, OutputInterface $output)
    {
        if (!$input->getArgument('account')) {
            $account = $this->getHelper('dialog')->askAndValidate(
                    $output,
                    'Please choose an account: ',
                    function($account) {
                        if (empty($account)) {
                            throw new \Exception('Account can not be empty');
                        }

                        return $account;
                    }
                    );
                    $input->setArgument('account', $account);
        }

        if (!$input->getArgument('email')) {
            $email = $this->getHelper('dialog')->askAndValidate(
                    $output,
                    'Please choose an email: ',
                    function($email) {
                        if (empty($email)) {
                            throw new \Exception('Email can not be empty');
                        }

                        return $email;
                    }
                    );
                    $input->setArgument('email', $email);
        }

        if (!$input->getArgument('amount')) {
            $amount = $this->getHelper('dialog')->askAndValidate(
                    $output,
                    'Please choose an amount: ',
                    function($amount) {
                        if (empty($amount)) {
                            throw new \Exception('Amount can not be empty');
                        }

                        return $amount;
                    }
                    );
                    $input->setArgument('amount', $amount);
        }

        if (!$input->getArgument('currency')) {
            $default = $this->container->get('adyen.service')->getDefaultCurrency();
            $currency = $this->getHelper('dialog')->askAndValidate(
                    $output,
                    'Please choose a currency [<comment>'.$default.'</comment>]: ',
                    function($currency) {
                        if (empty($currency)) {
                            return null;
                        }

                        return $currency;
                    }
                    );
                    $input->setArgument('currency', $currency);
        }
    }
}
