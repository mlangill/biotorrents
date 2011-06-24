#######################
#Warning! This should not be the first line in the script!
#Four lines of code are added when the user downloads this script from BioTorrents, so that it becomes personalized for that user.
#For example the beginning of the program should look like this:
##!/usr/bin/perl
#my $uid     = 'xxx';
#my $pass    = 'xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx';
#my $passkey = 'xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx';
#######################


##############################
#Given a directory this creates a torrent (using mktorrent) and uploads it to www.BioTorrents.net

#Input: a directory or file. 
#If it is a directory, then the directory can contain any files (compressed or not), but must have a README file (uncompressed) containing a description of the files (otherwise the file that contains the description can be explicity stated by using the -d option).
#If it is a file, then it must be a compressed archive (tar.gz, tar.bz2, or .zip), and it must contain a README file within the archive. 
#
#Output: a .torrent file (which should be used to start seeding the torrent)

#Requirements:
#-mktorrent installed and on the users PATH
#-Perl
#-internet connection

#Written by
#Morgan Langille (mlangille@ucdavis.edu)
#October 19, 2009
#Last Updated:
#May 30, 2010
##############################

use warnings;
use strict;

use LWP;
use HTTP::Cookies;
use File::Basename;
use Getopt::Long;
use HTTP::Request::Common;
use Cwd;

#BioTorrents categories
#Numerical ordered key, Name of category, actual category id in Biotorrents
my %category_choices = (
	1 => [ 'Miscellaneous',   12 ],
	2 => [ 'Genomics',        4 ],
	3 => [ 'Metabolomics',    7 ],
	4 => [ 'Papers',          17 ],
	5 => [ 'Phylogenetics',   1 ],
	6 => [ 'Proteomics',      6 ],
	7 => [ 'Transcriptomics', 5 ],
        8 => [ 'Chemistry',18],
        9 => [ 'Physics', 19],
    10 => [ 'Bioinformatics',2],
    11 => ['Metagenomics',3],
);

my %license_choices = (
    1 =>"Public Domain",
    2 =>"Creative Commons Attribution",
    3 =>"Creative Commons Attribution Share Alike",
    4 =>"Creative Commons Attribution No Derivatives",
    5 =>"Creative Commons Attribution Non-Commercial",
    6 =>"Creative Commons Attribution Non-Commercial Share Alike",
    7 =>"Creative Commons Attribution Non-Commercial No Derivatives",
    8 =>"GNU General Public License",
    9 =>"The GNU Lesser General Public License",
    10 =>"The BSD License",    
    11 =>"Other",
    12 =>"Creative Commons Zero",
    13 =>"Open Data Commons Public Domain Dedication and License",
    );
#command line options
my $output_dir = cwd();
my $torrent_name_option;
my $upload_name_option;
my $description_file='';
my $web_seed;
my $category;
my $license;
my $version;
my $help = '';

GetOptions(
	"output_dir=s"   => \$output_dir,
	"torrent_name=s" => \$torrent_name_option,
	"upload_name=s"  => \$upload_name_option,
        "description=s"  => \$description_file,
	"category=i"     => \$category,
        "license=i"      => \$license,
        "version=i"      => \$version,
        "web_seed=s"     => \$web_seed,
	"help"           => \$help
);

#Create usage
my $usage = "Usage: upload_to_biotorrents_as_xxxx.pl [-o -t -u -d -v -w -h] -c <category #> -l <license #> <directory or file>\n\n";

#category usage
my $category_usage = "\nCategory choices are:\n";
foreach my $key ( sort {$a <=> $b}( keys %category_choices ) ) {
	$category_usage .= $key . "=" . $category_choices{$key}[0] . "\n";
}

#license usage
my $license_usage = "\nLicense choices are:\n";
foreach my $key (sort {$a <=> $b}(keys %license_choices)){
    $license_usage .= $key . "=". $license_choices{$key}."\n";
}

#full usage
my $long_usage='Options:
-c, --category=<n>             : Specify the category number. See list below. (Mandatory)
-l, --license=<n>              : Specify the license number. See list below. (Mandatory)
-o, --output_dir=<directory>   : Specify the location to output the torrent file (default is current directory)
-t, --torrent_name=<file_name> : Specify the torrent file name (default is to use directory name)
-u, --upload_name=<name>       : Specify the title of the upload on BioTorrents (default is to use the torrent name)
-d, --description=<file_name>  : Specify the file that contains the description of the torrent (default is to search for a file named "README") 
-v, --version=<n>              : Specify the torrent id that this torrent is a newer version of (optional)
-w, --web_seed=<url>           : Specify the url (http or ftp) that contains the location of these files to act as a web seed (optional)
-h, --help                     : Show all options
';

my @input = @ARGV;

if ($help) {
	print $usage . $long_usage . $category_usage . $license_usage;
	exit;
}

unless (@input) {
	print $usage ."Use -h for help.\n";
	exit;
}


#ensure the user selects a valid category
unless ($category && exists($category_choices{$category})) {
	print $usage . $category_usage;
	exit;
}

unless($license){
    print $usage . $license_usage;
    exit;
}

INPUT: foreach my $dataset (@input){
	
    unless ( -d $dataset || -e $dataset) {
	print "The dataset $dataset does not exist!\n";
	exit;
    }
    
    my $description;
    my $readme;
    #Take description from command line option (-d)
    if(-e $description_file){
	$readme=$description_file;
        #load the README file into a string
	open( my $README, '<', $readme ) || die "The file: $readme is not readable!$!";
	my @README = <$README>;
	$description = join( '', @README );
	close($README);
    }else{
	
#do the following if the dataset is a directory
	if(-d $dataset){
	
#search for the README file
	    my @readme = glob $dataset . "/README*";
	    if ( @readme > 1 ) {
		print
		    "$dataset contains more than one README file! I don't know which one to use for torrent description.\n";
		next INPUT;
	    }
	    
#complain if the README file doesn't exist or is empty
	    $readme = $readme[0];
	    if ( !( -e $readme ) || -z $readme ) {
		print
		    "The directory $dataset must contain a file called README (with any extension) describing the torrent.\n";
		next INPUT;
	    }
	    #load the README file into a string
	    open( my $README, '<', $readme ) || die "The file: $readme is not readable!$!";
	    my @README = <$README>;
	    $description = join( '', @README );
	    close($README);

	    
	}else{

	    #seach in gzip tarballs
	    if($dataset =~ /.*\.(tar.gz)|(tgz)/){
		my @README = `tar -xzOf $dataset --wildcards --ignore-case *readme*`;
		if(@README == 0){
		    print "No REAMDE file exists or is empty in the dataset: $dataset. Ensure that there is a README file with a description in it or use the -d option.\n";
		    next INPUT;
		}
		$description = join( '', @README );
		
		#search in bzip tarballs
	    }elsif($dataset =~ /.*\.(tar.bz2)|(tbz)|(tb2)/){
		my @README = `tar -xjOf $dataset --wildcards --ignore-case *readme*`;
		if(@README == 0){
		    print "No REAMDE file exists or is empty in the dataset: $dataset. Ensure that there is a README file with a description in it or use the -d option.\n";
		    next INPUT;
		}
		$description = join( '', @README );

		#search in non-compressed tarballs
	    }elsif($dataset =~ /.*\.(tar)/){
		my @README = `tar -xOf $dataset --wildcards --ignore-case *readme*`;
		if(@README == 0){
		    print "No REAMDE file exists or is empty in the dataset: $dataset. Ensure that there is a README file with a description in it or use the -d option.\n";
		    next INPUT;
		}
		$description = join( '', @README );
		
		#search in zipped directories
	    }elsif($dataset =~ /.*\.(zip)/){
		my @README = `unzip -p -C $dataset *readme*`;
		if(@README == 0){
		    print "No REAMDE file exists or is empty in the dataset: $dataset. Ensure that there is a README file with a description in it or use the -d option.\n";
		    next INPUT;
		}
	    $description = join( '', @README );
	    }else{
		print "Sorry I don't know how to find the README file for the dataset: $dataset,  so that I can use it for the torrent description.\n";
		next INPUT;
	    }
	}
    }
    
#check if we can write to the output directory
    die
	"Can't create torrent file in $output_dir. You do not have write permission!\n"
	unless ( -w $output_dir );
    
#create the torrent unless given by the user
    my $torrent_name;
    if($torrent_name_option) {
	$torrent_name = $torrent_name_option;
    }else{
	my $dir_name = basename($dataset);
	$torrent_name = $dir_name . '.torrent';
    }
    
#torrent file name and location
    my $torrent = $output_dir . '/' . $torrent_name;
    
#Remove the torrent file if it already exists
    if(-e $torrent){
	unlink $torrent;
    }
    
    my $tracker_announce_url =
	'http://www.biotorrents.net/announce.php?passkey=' . $passkey;
    
#get the size of the directory
    my ($dir_size) = split(/\t/,`du -s $dataset`);
#2^n (eg. 2^20 = 1MB)
    my $piece_size=19;
#if bigger than 2GB use a larger piece size
    if($dir_size > 2*1024*1024){
	$piece_size=20;
    }elsif($dir_size > 4*1024*1024){
	$piece_size=21;
    }
    
#create the torrent file with mktorrent
    my $mktorrent_cmd = "mktorrent -a $tracker_announce_url -o $torrent -l $piece_size ";
    
    #add web seed url if given
    if(defined($web_seed)){
	$mktorrent_cmd .= "-w $web_seed ";
    }
    $mktorrent_cmd.=$dataset;
    
    `$mktorrent_cmd`;
    
    #debug
    #next INPUT;

#use torrent name unless set by user\
    my $upload_name;
    if ($upload_name_option) {
	$upload_name = $upload_name_option;
    }else{
	($upload_name) = fileparse($torrent_name,'.torrent');
    }
    
#set biotorrents category id
    my $category_id = $category_choices{$category}[1];
    
#Prepare the HTML POST
    my $cookie_jar = HTTP::Cookies->new;
    $cookie_jar->set_cookie( 0, 'uid',  $uid,  '/', 'www.biotorrents.net' );
    $cookie_jar->set_cookie( 0, 'pass', $pass, '/', 'www.biotorrents.net' );
    
    my $ua = LWP::UserAgent->new;
    $ua->cookie_jar($cookie_jar);
    
    my $req = (POST 'http://www.biotorrents.net/takeupload.php',
	       Content_Type => 'form-data', 
	       Content =>	  [
		   'file'          => [$torrent],
		   'name'          => $upload_name,
		   'descr'         => $description,
		   'type'          => $category_id,
		   'lic'           => $license,
		   'version'       => $version,
		'MAX_FILE_SIZE' => '4000000'
	       ]
	);
    
#send the request
    my $res = $ua->request($req);
    my $content = $res->content;
    
#Check if the upload is successful (this is probably not the most robust checking)
    if ( $content =~ /(Upload failed)|(Fatal error)/ ) {
	print "Upload of torrent failed!\n";
	print "Response from website was: $content\n";
    } else {
	print "Upload Successful! Please start seeding the torrent now.\n";
    }
    
}
