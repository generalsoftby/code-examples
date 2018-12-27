<?php

namespace App\Factory;

use App\Document\Contact;
use App\Document\Field\Address;
use App\Document\Field\BankAccount;
use App\Document\Field\Calendar;
use App\Document\Field\Currency;
use App\Document\Field\Email;
use App\Document\Field\MultiSelect;
use App\Document\Field\Select;
use App\Document\Field\TextArea;
use App\Document\Field\TextInput;
use App\Document\Field\Value\AddressFields;
use App\Document\Field\Value\BankAccountFields;
use App\Document\Field\Value\CalendarFields;
use App\Document\Label;

/**
 *
 */
class ContactFactory extends AbstractFactory
{
    private const LABELS = [
        'Sponsor',
        'Visitor'
    ];

    /**
     * @inheritdoc
     */
    protected function create(array $defaults = [])
    {
        $contact = (new Contact())
            ->addId($this->faker->randomNumber(9), 'reservix')
            ->setType($this->faker->boolean() ? Contact::TYPE_B2B : Contact::TYPE_B2C)
        ;

        $contact->addKeyword('Businesspartner');
        $contact->addKeyword($this->faker->text(15));

        $this->addBaseLabel($contact);

        $labels = $this->faker->randomElements(self::LABELS, $this->faker->numberBetween(0, 2));

        foreach ($labels as $label) {
            $method = 'add' . $label . 'Label';
            $this->{$method}($contact);
        }

        $this->mergeWithDefault($contact, $defaults);

        return $contact;
    }

    /**
     * @param Contact $contact
     *
     * @return $this
     */
    private function addBaseLabel(Contact $contact)
    {
        $label = (new Label('base'))
            ->addField(new TextInput('firstname', $this->faker->firstName))
            ->addField(new TextInput('lastname', $this->faker->lastName))
            ->addField(new Email('email', $this->faker->email))
            ->addField(new TextInput('phonenumber', $this->faker->numerify('(###)###-##-##')))
            ->addField(new TextInput('mobilephonenumber', $this->faker->numerify('(###)###-##-##')))
            ->addField(new Calendar('birthday', [
                new CalendarFields(
                    $this->faker->dateTimeBetween('-40 years', '-18 years')
                )
            ]))
            ->addField(new Address('address',
                new AddressFields(
                    $this->faker->streetName . ' ' . $this->faker->buildingNumber,
                    $this->faker->postcode, $this->faker->city,
                    $this->faker->state,
                    $this->faker->countryCode
                )
            ))
        ;

        $contact->addLabel($label);

        return $this;
    }

    /**
     * @param Contact $contact
     *
     * @return $this
     */
    private function addSponsorLabel(Contact $contact)
    {
        $label = (new Label('sponsor'))
            ->addField(new Calendar('contractterm', [
                new CalendarFields(
                    new \DateTime(),
                    $this->faker->dateTimeBetween('now', '+1 year')
                )
            ]))
            ->addField(
                (new Currency('sponsoringvolume', $this->faker->numberBetween(1000, 99999)))
                    ->setCurrency('EUR')
            )
            ->addField(new BankAccount('bankaccount',
                    new BankAccountFields(
                        $this->faker->bankAccountNumber,
                        $this->faker->company,
                        $this->faker->numberBetween(10000, 99999),
                        $this->faker->swiftBicNumber,
                        $this->faker->postcode,
                        $this->faker->lastName
                    )
                )
            )
        ;

        $contact->addLabel($label);

        return $this;
    }

    /**
     * @param Contact $contact
     *
     * @return $this
     */
    private function addVisitorLabel(Contact $contact)
    {
        $label = (new Label('visitor'))
            ->addField(new TextArea('note', $this->faker->text(50)))
            ->addField(new Select('gender', $this->faker->randomElement(['Male', 'Female'])))
            ->addField(new MultiSelect('interest',  $this->faker->randomElements(FieldSettingFactory::GENRES, $this->faker->numberBetween(1, 4))))
        ;

        $contact->addLabel($label);

        return $this;
    }

}
