require 'sinatra'
require 'sinatra/json'
require "sinatra/cors"
require 'sequel'
require 'json'

DB = Sequel.connect('sqlite://api.db')

DB.create_table? :tones do
  primary_key :id
  String :title
end

DB.create_table? :settings do
  primary_key :id
  String :default_model, null: false
  String :api_chat_gpt
  String :api_claude
  String :morning_text_header
  String :morning_text_before
  String :morning_text_block_header
  String :morning_text_after
  String :evening_text_header
  String :evening_text_before
  String :evening_text_block_header
  String :evening_text_after
  DateTime :created_at
  DateTime :updated_at
end

set :allow_origin, "http://localhost:3000"
set :allow_methods, "GET,HEAD,POST,PUT,DELETE,OPTIONS"
set :allow_headers, "content-type,if-modified-since"

class Tone < Sequel::Model
  plugin :json_serializer
end

class Settings < Sequel::Model
  plugin :json_serializer
end

get '/tones' do
  tones = Tone.all
  json tones
end

get '/tones/:id' do
  tone = Tone[params["id"]]
  json tone
end

post '/tones' do
  data = JSON.parse(request.body.read, symbolize_names: true)
  tone = Tone.create(title: data[:title])
  json tone
end

put '/tones/:id' do
  data = JSON.parse(request.body.read, symbolize_names: true)
  tone = Tone[params["id"]]
  tone.update(title: data[:title])
  json tone
end

delete '/tones/:id' do
  tone = Tone[params["id"]]
  tone.delete
  json tone
end

post '/settings' do
  data = JSON.parse(request.body.read, symbolize_names: true)
  settings = Settings.first || Settings.new
  settings.set(data)
  settings.save
  json settings
end

get '/settings' do
  settings = Settings.first
  json settings || {}
end

put '/settings' do
  data = JSON.parse(request.body.read, symbolize_names: true)
  settings = Settings.first
  if settings
    settings.update(data)
    json settings
  else
    status 404
    json({ error: "Settings not found" })
  end
end

delete '/settings' do
  settings = Settings.first
  if settings
    settings.delete
    json({ message: "Settings reset to default" })
  else
    status 404
    json({ error: "Settings not found" })
  end
end