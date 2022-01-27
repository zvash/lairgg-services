local next_sequence = redis.call("incr", KEYS[1])
local last_sequence = ARGV[1] * 1
if next_sequence > last_sequence then
    return next_sequence
else
    next_sequence = last_sequence + 1
    redis.call("set", KEYS[1], last_sequence)
    return next_sequence
end
