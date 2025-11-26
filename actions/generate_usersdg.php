<?php
// Generate 14850 synthetic Indian names into pages/usersdg.tsv (name-only header)
$path = __DIR__ . '/../pages/usersdg.tsv';
$first = [
  'Amit','Sumit','Rohit','Rahul','Vikas','Vivek','Karan','Ajay','Sanjay','Deepak','Ankit','Arjun','Pankaj','Rakesh','Manoj','Dinesh','Gaurav','Prakash','Subhash','Hemant','Yogesh','Vikram','Aakash','Rajat','Harsh','Nitin','Kunal','Saurabh','Ashish','Abhishek','Pradeep','Suraj','Naresh','Jitendra','Anand','Akash','Kapil','Tarun','Umesh','Ravindra','Devendra','Sachin','Shyam','Naveen','Ravi','Shivam','Mahesh','Mukesh','Jagdish','Lokesh','Alok','Arvind','Bhupendra','Chandan','Chirag','Farhan','Imran','Irfan','Rizwan','Yusuf','Aman','Gulshan','Kushal','Sarvesh','Kabir','Lalit','Kuldeep','Gurmeet','Jaswant','Rajkumar','Rajendra','Ram','Gopal','Harinder','Kailash','Khushal','Mohan','Monu','Naseem','Nawaz','Parvesh','Punit','Sameer','Sandeep','Santosh','Sevak','Shankar','Shyam','Sunil','Tejas','Vineet','Zubair','Afsar','Mustafa','Nitin','Nitish'
];
$last = [
  'Sharma','Verma','Gupta','Yadav','Singh','Khan','Ansari','Patel','Chauhan','Mehta','Jain','Bansal','Aggarwal','Goel','Kapoor','Malik','Saxena','Srivastava','Kashyap','Bhatt','Nair','Reddy','Iyer','Menon','Das','Ghosh','Mitra','Sen','Banerjee','Roy','Pillai','Shetty','Kulkarni','Kamat','Pandit','Thakur','Mandal','Mishra','Tiwari','Tripathi','Chatterjee','Chandra','Sarkar','Soni','Saxena'
];

$rows = ["name"];
$used = [];
$target = 14850;
for ($i=0; $i<$target; $i++) {
    $fn = $first[$i % count($first)];
    $ln = $last[$i % count($last)];
    $name = $fn . ' ' . $ln;
    // diversify every 7th with extra middle token
    if ($i % 7 === 0) { $name = $fn . ' ' . strtolower($ln) . ' ' . $ln; }
    // ensure uniqueness
    $base = $name;
    $ctr = 1;
    while (isset($used[$name])) { $name = $base . ' ' . $ctr; $ctr++; }
    $used[$name] = true;
    $rows[] = $name;
}
file_put_contents($path, implode("\n", $rows) . "\n");
echo "Generated " . ($target) . " names into pages/usersdg.tsv\n";
?>