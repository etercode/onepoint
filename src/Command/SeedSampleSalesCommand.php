<?php

namespace App\Command;

use App\Entity\Consultation;
use App\Entity\Order;
use App\Entity\OrderItem;
use App\Entity\Product;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Seeds sample orders (with line items referencing real products) and
 * consultation requests, so the admin dashboard/orders/customers/consultations
 * screens have data. Idempotent unless --fresh is passed.
 */
#[AsCommand(name: 'app:sample:seed', description: 'Seed sample orders and consultations for the admin area')]
class SeedSampleSalesCommand extends Command
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly ProductRepository $products,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addOption('fresh', null, InputOption::VALUE_NONE, 'Delete existing orders/consultations before seeding');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $fresh = (bool) $input->getOption('fresh');

        $existing = $this->em->getRepository(Order::class)->count([]);
        if ($existing > 0 && !$fresh) {
            $io->warning(sprintf('Orders already exist (%d). Re-run with --fresh to wipe and reseed.', $existing));

            return Command::SUCCESS;
        }

        if ($fresh) {
            $this->em->createQuery('DELETE FROM '.OrderItem::class.' i')->execute();
            $this->em->createQuery('DELETE FROM '.Order::class.' o')->execute();
            $this->em->createQuery('DELETE FROM '.Consultation::class.' c')->execute();
            $io->note('Existing orders and consultations deleted.');
        }

        // Products indexed 1..N by ascending id, so seed definitions can refer to
        // stable positions regardless of the actual id values.
        $byPosition = $this->products->findBy(['deletedAt' => null], ['id' => 'ASC']);
        if ([] === $byPosition) {
            $io->error('No products found. Run app:catalog:seed first.');

            return Command::FAILURE;
        }

        $orderCount = $this->seedOrders($byPosition);
        $consultationCount = $this->seedConsultations();

        $this->em->flush();

        $io->success(sprintf('Seeded %d orders and %d consultations.', $orderCount, $consultationCount));

        return Command::SUCCESS;
    }

    /**
     * @param list<Product> $byPosition
     */
    private function seedOrders(array $byPosition): int
    {
        // [number, name, email, status, date, [position => qty, ...]]
        $defs = [
            ['OP-1042', 'Leyla Həsənova', 'leyla@mail.az', 'delivering', '2026-06-28', [2 => 1]],
            ['OP-1041', 'Rəşad Quliyev', 'rashad@mail.az', 'confirmed', '2026-06-27', [19 => 1, 20 => 1]],
            ['OP-1040', 'Nərgiz Əliyeva', 'nargiz@mail.az', 'pending', '2026-06-27', [6 => 1, 9 => 1, 21 => 1]],
            ['OP-1039', 'Tural İsmayılov', 'tural@mail.az', 'completed', '2026-06-26', [1 => 1]],
            ['OP-1038', 'Aysel Məlikova', 'aysel@mail.az', 'completed', '2026-06-25', [24 => 1]],
            ['OP-1037', 'Orxan Cabbarov', 'orxan@mail.az', 'cancelled', '2026-06-24', [22 => 1]],
            ['OP-1036', 'Nərgiz Əliyeva', 'nargiz@mail.az', 'completed', '2026-06-20', [6 => 1, 30 => 2]],
            ['OP-1035', 'Nərgiz Əliyeva', 'nargiz@mail.az', 'completed', '2026-05-15', [28 => 1]],
            ['OP-1034', 'Leyla Həsənova', 'leyla@mail.az', 'completed', '2026-05-10', [3 => 1, 22 => 4]],
        ];

        $count = count($byPosition);
        foreach ($defs as [$number, $name, $email, $status, $date, $lines]) {
            $order = (new Order())
                ->setOrderNumber($number)
                ->setCustomerName($name)
                ->setCustomerEmail($email)
                ->setStatus($status)
                ->setPlacedAt(new \DateTimeImmutable($date));

            $total = 0;
            $itemCount = 0;
            foreach ($lines as $position => $qty) {
                if ($position < 1 || $position > $count) {
                    continue;
                }
                $product = $byPosition[$position - 1];
                $item = (new OrderItem())
                    ->setOrderRef($order)
                    ->setProduct($product)
                    ->setProductName($product->getName())
                    ->setUnitPrice($product->getPrice())
                    ->setQuantity($qty);
                $this->em->persist($item);

                $total += $product->getPrice() * $qty;
                $itemCount += $qty;
            }

            $order->setTotal($total)->setItemCount($itemCount);
            $this->em->persist($order);
        }

        return count($defs);
    }

    private function seedConsultations(): int
    {
        // [name, phone, room, message, status]
        $defs = [
            ['Aysel Quliyeva', '+994 50 111 22 33', 'living-room', 'Qonaq otağı üçün 3 yerli divan və jurnal masası axtarıram.', 'new'],
            ['Murad Əliyev', '+994 55 222 33 44', 'bedroom', 'Yataq dəsti və paltar dolabı üçün ölçü məsləhəti lazımdır.', 'new'],
            ['Günel Hüseynova', '+994 70 333 44 55', 'kitchen', 'Mətbəx üçün masa və 6 stul.', 'contacted'],
            ['Elvin Məmmədov', '+994 51 444 55 66', 'kids', 'Uşaq otağı üçün gənc çarpayı və yazı masası.', 'closed'],
            ['Sənəm Kərimli', '+994 77 555 66 77', 'office', 'Ev ofisi üçün ergonomik stul və masa.', 'new'],
        ];

        foreach ($defs as [$name, $phone, $room, $message, $status]) {
            $consultation = (new Consultation())
                ->setName($name)
                ->setPhone($phone)
                ->setRoom($room)
                ->setMessage($message)
                ->setStatus($status);
            $this->em->persist($consultation);
        }

        return count($defs);
    }
}
