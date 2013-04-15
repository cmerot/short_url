server 'localhost', :app, :web, :db, :primary => true

def set_files_tpl_params
  set :files_tpl_params, {
    :server   => {
      :hostname     => "#{stage}.#{tld}",
      :fastcgi_pass => "unix:/var/run/php5-fpm.sock",
    },
    :debug    => true,
    :database => {
      :driver   => "pdo_mysql",
      :dbname   => "#{application}_#{stage}",
      :user     => "#{application}_#{stage}",
      :password => Capistrano::CLI.password_prompt("Enter database password: "),
      :host     => "localhost"
    },
    :short_url => {
      :mountpoint =>    '/',
      :alphabet   =>    '123456789abcdefghijkmnopqrstuvwxyzABCDEFGHJKLMNPQRSTUVWXYZ',
    },
    :google_oauth => {
      :mountpoint    => '/',
      :client_id     => Capistrano::CLI.ui.ask("Enter Google Oauth client id: "),
      :client_secret => Capistrano::CLI.password_prompt("Enter Google Oauth client secret: "),
    }
  }
end
